import Alpine from 'alpinejs';
import intersect from '@alpinejs/intersect';
import axios from 'axios';

window.Alpine = Alpine;
window.axios = axios;
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

Alpine.plugin(intersect);
Alpine.start();
