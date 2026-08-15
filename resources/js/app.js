import Alpine from 'alpinejs';
import leadsBoard from './leads-board';

window.Alpine = Alpine;

Alpine.data('leadsBoard', leadsBoard);

Alpine.start();
