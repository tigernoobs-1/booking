import type { AxiosResponse } from 'axios'
import { defineStore } from 'pinia'

import type { Facilities } from '@/@fake-db/types'
import type { FacilityParams, FacilityProperties, UserProperties, NoticeProperties, BookingProperties, ConfigTime, ServiceRequest } from '@/views/apps/admin/types'
import { useApi } from '@/plugins/axios'

const api = useApi()

export const useFacilityListStore = defineStore('FacilityListStore', {
  actions: {

    // 👉 Fetch Facility data
    fetchFacility() { return api.get('item') },

    // 👉 Add Facility
    addFacility(facilityData: FacilityProperties) {
      return new Promise((resolve, reject) => {
        api.post('item', facilityData).then(response => resolve(response))
          .catch(error => reject(error))
      })
    },

    // 👉 Delete Facility
    deleteFacility(id: number) {
      return new Promise<AxiosResponse>((resolve, reject) => {
        api.delete(`item/${id}`).then(response => resolve(response)).catch(error => reject(error))
      })
    },

    // 👉 Update Facility
    updateFacility(facilityData: FacilityProperties, id: number) {
      return new Promise<AxiosResponse>((resolve, reject) => {
        api.put(`item/${id}`, facilityData).then(response => resolve(response)).catch(error => reject(error))
      })
    },

    // 👉 Fetch Notice data
    fetchNotice() { return api.get('notice') },

    // 👉 Add Notice
    addNotice(noticeData: NoticeProperties) {
      return new Promise((resolve, reject) => {
        api.post('notice', noticeData).then(response => resolve(response))
          .catch(error => reject(error))
      })
    },

     // 👉 Update Notice
    updateNotice(noticeData: NoticeProperties, id: number) {
      return new Promise<AxiosResponse>((resolve, reject) => {
        api.put(`notice/${id}`, noticeData).then(response => resolve(response)).catch(error => reject(error))
      })
    },

    deleteNotice(id: number) {
      return new Promise<AxiosResponse>((resolve, reject) => {
        api.delete(`notice/${id}`).then(response => resolve(response)).catch(error => reject(error))
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
    deleteUser(id: number) {
       return new Promise<AxiosResponse>((resolve, reject) => {
        api.delete(`user/${id}`).then(response => resolve(response)).catch(error => reject(error))
      })
    },

    // 👉 complain
    fetchComplaint() { return api.get('complain') },

    deleteComplaint(id: number) {
      return new Promise<AxiosResponse>((resolve, reject) => {
        api.delete(`complain/${id}`).then(response => resolve(response)).catch(error => reject(error))
      })
    },

    // 👉 Booking user
    fetchBookingAdmin() { return api.get('booking-admin') },
    // 👉 Booking user
    fetchBookingUser() { return api.get('booking-user') },
    

    addBooking(bookingData: BookingProperties) {
      return new Promise((resolve, reject) => {
        api.post('booking', bookingData).then(response => resolve(response))
          .catch(error => reject(error))
      })
    },

    deleteBooking(bookingData: BookingProperties,id: number) {
      return new Promise<AxiosResponse>((resolve, reject) => {
        api.put(`delete-booking/${id}`, bookingData).then(response => resolve(response)).catch(error => reject(error))
      })
    },

    updateBooking(bookingData: BookingProperties, id: number) {
      return new Promise<AxiosResponse>((resolve, reject) => {
        api.put(`booking/${id}`, bookingData).then(response => resolve(response)).catch(error => reject(error))
      })
    },

    //Config
    updateTimeConfig(configData: ConfigTime, id: number) {
      return new Promise<AxiosResponse>((resolve, reject) => {
        api.put(`config-time/${id}`, configData).then(response => resolve(response)).catch(error => reject(error))
      })
    },


    addConfig(configData: ConfigTime) {
      return new Promise((resolve, reject) => {
        api.post('config', configData).then(response => resolve(response))
          .catch(error => reject(error))
      })
    },

    deleteConfig(id: number) {
      return new Promise<AxiosResponse>((resolve, reject) => {
        api.delete(`config/${id}`).then(response => resolve(response)).catch(error => reject(error))
      })
    },

    fetchTimeConfig() { return api.get('config-time') },

    fetchDayConfig() { return api.get('config-day') },

    //Service Request

    addServiceRequest(serviceData: ServiceRequest) {
      return new Promise((resolve, reject) => {
        api.post('service', serviceData).then(response => resolve(response.data))
          .catch(error => reject(error))
      })
    },

    updateServiceRequest(serviceData: ServiceRequest, id: number) {
      return new Promise<AxiosResponse>((resolve, reject) => {
        api.put(`service/${id}`, serviceData).then(response => resolve(response)).catch(error => reject(error))
      })
    },

    deleteService(id: number) {
      return new Promise<AxiosResponse>((resolve, reject) => {
        api.delete(`service/${id}`).then(response => resolve(response)).catch(error => reject(error))
      })
    },

    fetchUserServiceRequest() { return api.get('service-user') },

    fetchUserWorkOrder() { return api.get('work-order') },

    fetchAdminService() { return api.get('service-admin') },

    fetchAdminWork() { return api.get('work-admin') },

    downloadFile(data:any) {
      return new Promise((resolve, reject) => {
        api.post('download-file', data, {responseType:'blob'}).then(response => resolve(response))
          .catch(error => reject(error))
      })
    },


  },
})

// 👉 fetch single Facility
/*  fetchUser(id: number) {
  return new Promise<AxiosResponse>((resolve, reject) => {
    axios.get(`/apps/users/${id}`).then(response => resolve(response)).catch(error => reject(error))
  })
}, */
