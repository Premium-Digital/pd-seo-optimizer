import '../scss/admin.scss';
import { initMetaGeneration  } from './admin/metaUpdater.js';
import { initAltGeneration  } from './admin/altGenerator.js';
jQuery.noConflict();

jQuery(function() {
    initMetaGeneration();
    initAltGeneration();
});