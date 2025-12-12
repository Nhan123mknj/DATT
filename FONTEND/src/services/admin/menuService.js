import apiClient from "../api/apiClient";

const ADMIN_URL = '/admin/menus';
const PUBLIC_URL = '/menus';

export const menuService = {
  /**
   * ========== PUBLIC ENDPOINTS ==========
   */

  /**
   * Get menu by slug (for frontend navigation)
   * @param {string} slug - Menu slug
   * @returns {Promise}
   */
  getBySlug(slug) {
    return apiClient.get(`${PUBLIC_URL}/${slug}`);
  },

  /**
   * ========== ADMIN ENDPOINTS ==========
   */

  /**
   * Get all menus (admin)
   * @returns {Promise}
   */
  list() {
    return apiClient.get(ADMIN_URL);
  },

  /**
   * Get menu with items (admin)
   * @param {number} id - Menu ID
   * @returns {Promise}
   */
  get(id) {
    return apiClient.get(`${ADMIN_URL}/${id}`);
  },

  /**
   * Create menu (admin)
   * @param {Object} data - Menu data
   * @returns {Promise}
   */
  create(data) {
    return apiClient.post(ADMIN_URL, data);
  },

  /**
   * Update menu (admin)
   * @param {number} id - Menu ID
   * @param {Object} data - Menu data
   * @returns {Promise}
   */
  update(id, data) {
    return apiClient.put(`${ADMIN_URL}/${id}`, data);
  },

  /**
   * Delete menu (admin)
   * @param {number} id - Menu ID
   * @returns {Promise}
   */
  delete(id) {
    return apiClient.delete(`${ADMIN_URL}/${id}`);
  },

  /**
   * ========== MENU ITEMS ENDPOINTS ==========
   */

  /**
   * Create menu item (admin)
   * @param {Object} data - Item data
   * @returns {Promise}
   */
  createItem(data) {
    return apiClient.post(`${ADMIN_URL}-items`, data);
  },

  /**
   * Update menu item (admin)
   * @param {number} id - Item ID
   * @param {Object} data - Item data
   * @returns {Promise}
   */
  updateItem(id, data) {
    return apiClient.put(`${ADMIN_URL}-items/${id}`, data);
  },

  /**
   * Delete menu item (admin)
   * @param {number} id - Item ID
   * @returns {Promise}
   */
  deleteItem(id) {
    return apiClient.delete(`${ADMIN_URL}-items/${id}`);
  },

  /**
   * Reorder menu items (admin)
   * @param {Array} items - Array of {id, sort_order}
   * @returns {Promise}
   */
  reorder(items) {
    return apiClient.post(`${ADMIN_URL}-items/reorder`, { items });
  },
};

