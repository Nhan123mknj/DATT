import apiClient from "../api/apiClient";

export const reservationsService = {
    getReservations(params = {}) {
        return apiClient.get("admin/reservations", { params });
    }
}
