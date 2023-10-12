import axios from '@axios'
import type { Event, NewEvent } from './types'

export const useCalendarStore = defineStore('calendar', {
  // arrow function recommended for full type inference
  state: () => ({
    availableCalendars: [
      {
        color: 'error',
        label: 'Helang - Meeting Room',
      },
      {
        color: 'success',
        label: 'Helang - War Room',
      },
    ],
    selectedCalendars: ['Helang - Meeting Room', 'Helang - War Room'],
  }),
  actions: {
    async fetchEvents() {
      const req = axios.get('/apps/calendar/events', { params: { calendars: this.selectedCalendars.join(',') } })
      console.log('req', (await req).request)
      return req
    },
    async addEvent(event: NewEvent) {
      return axios.post('/apps/calendar/events', { event })
    },
    async updateEvent(event: Event) {
      return axios.post('/apps/calendar/events/${event.id}', { event })
    },
    async removeEvent(eventId: string) {
      return axios.delete('/apps/calendar/events/${eventId}')
    },

  },
})
