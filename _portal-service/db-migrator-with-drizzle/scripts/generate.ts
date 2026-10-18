import { execSync } from 'child_process';
import * as readline from 'readline';
import { processSqlFiles } from './add-sql-comments';

const rl = readline.createInterface({ input: process.stdin, output: process.stdout });

rl.question('Migration name: ', (name) => {
  rl.close();
  const safeName = name.trim().replace(/\s+/g, '_');
  const cmd = safeName ? `bunx drizzle-kit generate --name=${safeName}` : 'bunx drizzle-kit generate';
  execSync(cmd, { stdio: 'inherit' });
  processSqlFiles();
});

