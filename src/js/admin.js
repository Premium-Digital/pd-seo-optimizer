import '../scss/admin.scss';
import { initMetaGeneration  } from './admin/metaUpdater.js';
jQuery.noConflict();

jQuery(function() {
    initMetaGeneration();
});