import '../scss/admin.scss';
import { initMetaGeneration  } from './admin/metaUpdater.js';
import { initAltGeneration  } from './admin/AltGenerator.js';
jQuery.noConflict();

jQuery(function() {
    initMetaGeneration();
    initAltGeneration();
});