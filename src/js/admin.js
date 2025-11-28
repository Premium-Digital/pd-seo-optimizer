import '../scss/admin.scss';
import { initMetaGeneration  } from './admin/metaUpdater.js';
import { initAltGeneration, initSingleAttachmentAlt, initMediaAltGeneration } from './admin/altGenerator.js';
jQuery.noConflict();

document.addEventListener('DOMContentLoaded', () => {
    initMetaGeneration();
    initAltGeneration();
    initSingleAttachmentAlt();
    initMediaAltGeneration();
});