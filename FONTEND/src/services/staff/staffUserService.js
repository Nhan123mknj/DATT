import apiClient from '../api/apiClient';

export const staffUserService = {
  search(params) {
    return apiClient.get('/staff/users', { params });
  },
};
