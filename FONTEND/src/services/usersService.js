import axios from "axios";
import apiClient from "./apiClient";
const API_BASE_URL = import.meta.env.VITE_API_BASE_URL; 

export const usersService = {
  getAllUser(page = 1, perPage , filters = {}) {
  return apiClient.get("/api/admin/users", {
    params: {
      page,
      per_page: perPage,
      ...filters, 
    },
  }); 
},

  getUserById(id) {
    return apiClient.get(`/api/admin/users/${id}`);
  },

  createUser(userData) {
    return apiClient.post("/api/admin/users", userData);
  },

  updateUser(id, userData) {
    return apiClient.put(`/api/admin/users/${id}`, userData);
  },

  deleteUser(id) {
    return apiClient.delete(`/api/admin/users/${id}`);
  },

  resetPassword(id) {
    return apiClient.post(`/api/admin/users/${id}/reset-password`);
  },

  toggleStatus(id) {
    return apiClient.patch(`/api/admin/users/${id}/toggle-status`);
  },
};
