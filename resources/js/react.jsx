import React from 'react';
import { createRoot } from 'react-dom/client';

export function mountReact(component, selector = '[data-react-app]') {
  const element = document.querySelector(selector);

  if (element) {
    createRoot(element).render(component);
  }
}

