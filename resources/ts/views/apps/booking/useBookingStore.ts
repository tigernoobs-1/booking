import type { AxiosResponse } from 'axios'
import { defineStore } from 'pinia'

import { useApi } from '@/plugins/axios'
import type { BookingProperties, UserProperties } from '@/views/apps/booking/types'

const api = useApi()

export const useBookingListStore = defineStore('BookingListStore', {
  actions: {

    // 👉 Fetch Facility data
    fetchBooking() { return api.get('booking') },

    // 👉 Add Booking
    addFacility(bookingData: BookingProperties) {
      return new Promise((resolve, reject) => {
        api.post('item', bookingData).then(response => resolve(response))
          .catch(error => reject(error))
      })
    },

    // 👉 Delete Booking
    deleteFacility(id: number) {
      return new Promise<AxiosResponse>((resolve, reject) => {
        api.delete(`item/${id}`).then(response => resolve(response)).catch(error => reject(error))
      })
    },

    // 👉 Update Booking
    updateBooking(bookingData: BookingProperties, id: number) {
      return new Promise<AxiosResponse>((resolve, reject) => {
        api.put(`item/${id}`, bookingData).then(response => resolve(response)).catch(error => reject(error))
      })
    },

    // 👉 Fetch User data
    fetchUser() { return api.get('user') },

    // 👉 Update User Role
    updateUser(userData: UserProperties, id: number) {
      return new Promise<AxiosResponse>((resolve, reject) => {
        api.put(`user/${id}`, userData).then(response => resolve(response)).catch(error => reject(error))
      })
    },

    fetchComplaint() { return api.get('complain') },

  },
})

