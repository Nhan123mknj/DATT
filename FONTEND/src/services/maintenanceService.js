import apiClient from '../services/api/apiClient';

export default {
  async getAll(params) {
    return apiClient.get('/admin/maintenances', { params });
  },
  async get(id) {
    return apiClient.get(`/admin/maintenances/${id}`);
  },
  async create(data) {
    return apiClient.post('/admin/maintenances', data);
  },

  async update(id, data) {
    return apiClient.put(`/admin/maintenances/${id}`, data);
  },
  async delete(id) {
    return apiClient.delete(`/admin/maintenances/${id}`);
  },
  async complete(id) {
    return apiClient.post(`/admin/maintenances/${id}/complete`);
  }
};
