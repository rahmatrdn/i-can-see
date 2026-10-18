import Viewer from 'viewerjs';
import 'viewerjs/dist/viewer.css';

let buktiTransferViewer = null;
let buktiDetailViewer = null;

const viewerOptions = {
    navbar: false,
    title: false,
    tooltip: true,
    movable: true,
    zoomable: true,
    zoomOnWheel: true,
    rotatable: false,
    scalable: false,
    toolbar: {
        zoomIn: 1,
        zoomOut: 1,
        oneToOne: 1,
        reset: 1,
        prev: 0,
        play: 0,
        next: 0,
        rotateLeft: 0,
        rotateRight: 0,
        flipHorizontal: 0,
        flipVertical: 0,
    },
};

window.openBuktiTransferViewer = function () {
    const image = document.getElementById('bukti-transfer-viewer-source');

    if (! image?.src) {
        return;
    }

    if (buktiTransferViewer) {
        buktiTransferViewer.destroy();
        buktiTransferViewer = null;
    }

    buktiTransferViewer = new Viewer(image, viewerOptions);
    buktiTransferViewer.show();
};

window.openBuktiDetailViewer = function (sourceId) {
    const image = document.getElementById(sourceId || 'bukti-detail-viewer-source');

    if (! image?.src) {
        return;
    }

    if (buktiDetailViewer) {
        buktiDetailViewer.destroy();
        buktiDetailViewer = null;
    }

    buktiDetailViewer = new Viewer(image, viewerOptions);
    buktiDetailViewer.show();
};

document.addEventListener('hidden.hs.overlay', (event) => {
    if (! ['modal-verifikasi', 'modal-verifikasi-up'].includes(event.target?.id) || ! buktiTransferViewer) {
        return;
    }

    buktiTransferViewer.destroy();
    buktiTransferViewer = null;
});
