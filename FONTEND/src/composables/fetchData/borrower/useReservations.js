import { storeToRefs } from "pinia";
import { useReservationStore } from "../../../stores/reservationStore";

export function useReservations() {
  const store = useReservationStore();
  const { reservations, pagination, isLoading } = storeToRefs(store);
  const { fetchReservations, cancelReservation } = store;

  const loadReservations = async (page = 1, filters = {}) => {
    await fetchReservations(page, filters);
  };

  return {
    reservations,
    pagination,
    isLoading,
    loadReservations,
    cancelReservation,
  };
}
