import { defineStore } from 'pinia';
import activityLogService from '@/services/admin/activityLogService';

export const useActivityLogStore = defineStore('activityLog', {
  state: () => ({
    activities: [],
    pagination: {
      current_page: 1,
      last_page: 1,
      per_page: 50,
      total: 0,
    },
    activityTypes: {},
    filters: {
      period: '7days',
      from_date: null,
      to_date: null,
      log_name: null,
      event: null,
      causer_id: null,
      search: '',
      page: 1,
    },
    loading: false,
    error: null,
  }),

  actions: {
    async fetchActivityLogs() {
      this.loading = true;
      this.error = null;
      
      try {
        const data = await activityLogService.getActivityLogs(this.filters);
        this.activities = data.activities;
        this.pagination = data.pagination;
        this.activityTypes = data.activity_types;
      } catch (error) {
        this.error = error.response?.data?.error || 'Failed to fetch activity logs';
        console.error('Error fetching activity logs:', error);
      } finally {
        this.loading = false;
      }
    },

    setFilter(key, value) {
      this.filters[key] = value;
      if (key !== 'page') {
        this.filters.page = 1;
      }
    },

    clearFilters() {
      this.filters = {
        period: '7days',
        from_date: null,
        to_date: null,
        log_name: null,
        event: null,
        causer_id: null,
        search: '',
        page: 1,
      };
    },

    setPage(page) {
      this.filters.page = page;
      this.fetchActivityLogs();
    },
  },
});
