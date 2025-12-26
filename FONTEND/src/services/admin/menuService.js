import apiClient from "../api/apiClient";

const ADMIN_URL = '/admin/menus';
const PUBLIC_URL = '/menus';

export const menuService = {
  getBySlug(slug) {
    return apiClient.get(`${PUBLIC_URL}/${slug}`);
  },


  list() {
    return apiClient.get(ADMIN_URL);
  },


  get(id) {
    return apiClient.get(`${ADMIN_URL}/${id}`);
  },


  create(data) {
    return apiClient.post(ADMIN_URL, data);
  },


  update(id, data) {
    return apiClient.put(`${ADMIN_URL}/${id}`, data);
  },


  delete(id) {
    return apiClient.delete(`${ADMIN_URL}/${id}`);
  },

  createItem(data) {
    return apiClient.post(`${ADMIN_URL}-items`, data);
  },


  updateItem(id, data) {
    return apiClient.put(`${ADMIN_URL}-items/${id}`, data);
  },


  deleteItem(id) {
    return apiClient.delete(`${ADMIN_URL}-items/${id}`);
  },


  reorder(items) {
    return apiClient.post(`${ADMIN_URL}-items/reorder`, { items });
  },
};


