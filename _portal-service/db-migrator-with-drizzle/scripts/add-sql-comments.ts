import fs from 'node:fs';
import path from 'node:path';

const BASE_DIR = path.resolve(import.meta.dir ?? __dirname, '..');
const SCHEMA_PATH = path.join(BASE_DIR, 'src/db/schema.ts');
const DRIZZLE_DIR = path.join(BASE_DIR, 'drizzle');

function parseSingleSchemaFile(filePath: string, tables: Map<string, Map<string, string>>) {
  if (!fs.existsSync(filePath)) return;

  const content = fs.readFileSync(filePath, 'utf-8');
  const lines = content.split('\n');

  let currentTable: string | null = null;
  let lastComment = '';

  for (let i = 0; i < lines.length; i++) {
    const rawLine = lines[i];
    const line = rawLine.trim();

    // Check for table start: mysqlTable('table_name', ...)
    const tableMatch = line.match(/mysqlTable\(\s*['"]([^'"]+)['"]/);
    if (tableMatch) {
      currentTable = tableMatch[1];
      if (!tables.has(currentTable)) {
        tables.set(currentTable, new Map());
      }
      lastComment = '';
      continue;
    }

    if (!currentTable) continue;

    // Check if line is a single-line comment
    if (line.startsWith('//')) {
      const commentText = line.replace(/^\/\/\s*/, '').trim();
      if (commentText) {
        lastComment = lastComment ? `${lastComment} ${commentText}` : commentText;
      }
    } else if (line.startsWith('/*') || line.startsWith('*')) {
      const cleaned = line
        .replace(/^\/\*\*?\s*/, '')
        .replace(/\*\/$/, '')
        .replace(/^\*\s*/, '')
        .trim();
      if (cleaned) {
        lastComment = lastComment ? `${lastComment} ${cleaned}` : cleaned;
      }
    } else if (line.includes(':')) {
      // Check for inline comment like: access_type: tinyint(), // 1: Super Admin
      let commentForThisField = lastComment;
      let lineWithoutInlineComment = line;
      const inlineIdx = line.indexOf('//');
      if (inlineIdx !== -1) {
        commentForThisField = line.substring(inlineIdx + 2).trim();
        lineWithoutInlineComment = line.substring(0, inlineIdx).trim();
      }

      if (commentForThisField) {
        // Match explicit DB column name: colName: varchar('custom_col_name', ...)
        const fieldMatch = lineWithoutInlineComment.match(
          /^([a-zA-Z0-9_]+)\s*:\s*[a-zA-Z0-9_]+\(\s*['"]([^'"]+)['"]/
        );
        let colName = '';
        if (fieldMatch) {
          colName = fieldMatch[2];
        } else {
          // Match simple field name: colName: varchar(...)
          const simpleFieldMatch = lineWithoutInlineComment.match(/^([a-zA-Z0-9_]+)\s*:/);
          if (simpleFieldMatch) {
            const fieldName = simpleFieldMatch[1];
            // Convert camelCase to snake_case if applicable
            colName = fieldName.replace(/[A-Z]/g, (letter) => `_${letter.toLowerCase()}`);
          }
        }

        if (colName) {
          tables.get(currentTable)!.set(colName, commentForThisField);
        }
      }
      lastComment = '';
    } else if (
      line !== '' &&
      !line.startsWith('//') &&
      !line.startsWith('/*') &&
      !line.startsWith('*')
    ) {
      lastComment = '';
    }
  }
}

export function parseCommentsFromSchema(schemaPath: string = SCHEMA_PATH): Map<string, Map<string, string>> {
  const tables = new Map<string, Map<string, string>>();
  const dir = path.dirname(schemaPath);
  const filesToScan: string[] = [];

  if (fs.existsSync(schemaPath)) {
    filesToScan.push(schemaPath);
  }

  if (fs.existsSync(dir)) {
    const dirFiles = fs.readdirSync(dir)
      .filter((f) => f.endsWith('.ts') && f !== 'index.ts' && path.join(dir, f) !== schemaPath)
      .map((f) => path.join(dir, f));
    filesToScan.push(...dirFiles);
  }

  for (const filePath of filesToScan) {
    parseSingleSchemaFile(filePath, tables);
  }

  return tables;
}

export function processSqlFiles(drizzleDir: string = DRIZZLE_DIR, schemaPath: string = SCHEMA_PATH) {
  if (!fs.existsSync(drizzleDir)) {
    console.log(`ℹ️ No drizzle directory found at ${drizzleDir}`);
    return;
  }

  const tablesMap = parseCommentsFromSchema(schemaPath);
  let totalCommentsCount = 0;
  for (const cols of tablesMap.values()) {
    totalCommentsCount += cols.size;
  }

  if (totalCommentsCount === 0) {
    console.log('ℹ️ No column comments found in schema.');
    return;
  }

  const sqlFiles = fs
    .readdirSync(drizzleDir)
    .filter((file) => file.endsWith('.sql'))
    .map((file) => path.join(drizzleDir, file));

  if (sqlFiles.length === 0) {
    console.log(`ℹ️ No .sql migration files found in ${drizzleDir}`);
    return;
  }

  for (const sqlFile of sqlFiles) {
    let content = fs.readFileSync(sqlFile, 'utf-8');
    const originalContent = content;

    // 1. Process CREATE TABLE blocks
    content = content.replace(
      /CREATE TABLE `([^`]+)` \(([\s\S]*?)\n\);/g,
      (fullMatch, tableName, tableBody) => {
        const cols = tablesMap.get(tableName);
        if (!cols || cols.size === 0) return fullMatch;

        let newBody = tableBody;
        for (const [colName, comment] of cols.entries()) {
          const sqlCommentEscaped = comment.replace(/'/g, "''");
          const colRegex = new RegExp(
            `^([\\t ]*\`${colName}\`[\\t ]+.*?)(\\s+COMMENT\\s+'(?:[^']|'')*')?(\\s*,?\\s*)$`,
            'gm'
          );
          newBody = newBody.replace(colRegex, (_line, def, _oldComment, comma) => {
            return `${def} COMMENT '${sqlCommentEscaped}'${comma}`;
          });
        }

        return `CREATE TABLE \`${tableName}\` (${newBody}\n);`;
      }
    );

    // 2. Process ALTER TABLE ADD statements (for future migrations)
    for (const [tableName, cols] of tablesMap.entries()) {
      for (const [colName, comment] of cols.entries()) {
        const sqlCommentEscaped = comment.replace(/'/g, "''");
        const alterRegex = new RegExp(
          `(\\bALTER TABLE \`${tableName}\` ADD (?:COLUMN )?\`${colName}\`[\\t ]+[^;\\n]+?)(?:\\s+COMMENT\\s+'(?:[^']|'')*')?(\\s*;?\\s*(?:--> statement-breakpoint)?)`,
          'g'
        );
        content = content.replace(alterRegex, (_line, def, suffix) => {
          const cleanDef = def.replace(/;\s*$/, '');
          const cleanSuffix = suffix.startsWith(';') ? suffix : (suffix ? ';' + suffix : ';');
          return `${cleanDef} COMMENT '${sqlCommentEscaped}'${cleanSuffix}`;
        });
      }
    }

    if (content !== originalContent) {
      fs.writeFileSync(sqlFile, content, 'utf-8');
      console.log(`✅ Added/updated SQL comments in: ${path.basename(sqlFile)}`);
    } else {
      console.log(`ℹ️ SQL comments already up to date in: ${path.basename(sqlFile)}`);
    }
  }
}

if (import.meta.main) {
  processSqlFiles();
}
