import apiClient from '../services/api/apiClient';

export default {
  async getAll(params) {
    return apiClient.get('/maintenances', { params });
  },
  async get(id) {
    return apiClient.get(`/maintenances/${id}`);
  },
  async create(data) {
    return apiClient.post('/maintenances', data);
  },

  async update(id, data) {
    return apiClient.put(`/maintenances/${id}`, data);
  },
  async delete(id) {
    return apiClient.delete(`/maintenances/${id}`);
  }
};
