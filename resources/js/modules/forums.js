import { createApp } from 'vue';
import '../../css/modules/forums.css';
import ForumsApp from './forums/App.vue';

const stateElement = document.getElementById('forums-initial-state');
const mountElement = document.querySelector('[data-forums-app]');
const initialState = stateElement ? JSON.parse(stateElement.textContent || '{}') : {};

if (mountElement) {
  createApp(ForumsApp, { initialState }).mount(mountElement);
}
