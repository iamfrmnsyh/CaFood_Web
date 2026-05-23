// Simple API client to communicate with backend/api.php
const API_BASE = './backend/api.php';

async function apiFetch(resource, options = {}) {
  const { id, method = 'GET', action, data } = options;
  let url = API_BASE + '?resource=' + encodeURIComponent(resource);
  if (id) url += '&id=' + encodeURIComponent(id);
  if (action) url += '&action=' + encodeURIComponent(action);

  const fetchOptions = { method, headers: {} };
  if (method !== 'GET' && data !== undefined) {
    fetchOptions.headers['Content-Type'] = 'application/json';
    fetchOptions.body = JSON.stringify(data);
  }

  const res = await fetch(url, fetchOptions);
  const text = await res.text();
  try { return JSON.parse(text); } catch(e) { return text; }
}

// Convenience helpers
const API = {
  getCategories: () => apiFetch('categories'),
  getCategory: (id) => apiFetch('categories', { id }),
  createCategory: (data) => apiFetch('categories', { method: 'POST', data }),
  updateCategory: (id, data) => apiFetch('categories', { id, method: 'PUT', data }),
  deleteCategory: (id) => apiFetch('categories', { id, method: 'DELETE' }),

  getMenus: () => apiFetch('menus'),
  getMenu: (id) => apiFetch('menus', { id }),
  createMenu: (data) => apiFetch('menus', { method: 'POST', data }),

  getStands: () => apiFetch('stands'),
  getStand: (id) => apiFetch('stands', { id }),
  createStand: (data) => apiFetch('stands', { method: 'POST', data }),


  register: (data) => apiFetch('users', { method: 'POST', action: 'register', data }),
  login: (data) => apiFetch('users', { method: 'POST', action: 'login', data }),

  createOrder: (data) => apiFetch('orders', { method: 'POST', data }),
  getOrders: () => apiFetch('orders'),
  getOrder: (id) => apiFetch('orders', { id }),

  createPayment: (data) => apiFetch('payments', { method: 'POST', data }),
};

window.API = API;
