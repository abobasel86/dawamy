import './bootstrap';

import Alpine from 'alpinejs';
import cameraApp from './cameraApp';

window.Alpine = Alpine;

Alpine.data('cameraApp', cameraApp);

Alpine.start();
