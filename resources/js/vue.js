import { createApp } from 'vue';

export function mountVue(component, selector = '[data-vue-app]', props = {}) {
  const element = document.querySelector(selector);

  if (element) {
    createApp(component, props).mount(element);
  }
}

