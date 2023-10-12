import mock from '@/@fake-db/mock'
import type { CalendarEvent } from '@/@fake-db/types'

const date = new Date()
const nextDay = new Date(new Date().getTime() + 24 * 60 * 60 * 1000)
const nextMonth = date.getMonth() === 11 ? new Date(date.getFullYear() + 1, 0, 1) : new Date(date.getFullYear(), date.getMonth() + 1, 1)
const prevMonth = date.getMonth() === 11 ? new Date(date.getFullYear() - 1, 0, 1) : new Date(date.getFullYear(), date.getMonth() - 1, 1)

const data: { events: CalendarEvent[] } = {
  events: [
    {
      id: '1',
      title: 'Helang - Meeting Room',
      start: date.toUTCString(),
      end: nextDay.toUTCString(),
      allDay: false,
      extendedProps: {
        email: 'test@example.com',
        phone: '0123456789',
        customerName: 'Iqbal Fikri',
        calendar: 'Helang - Meeting Room',
      },
    },
    {
      id: '2',
      title: 'Helang - War Room',
      start: new Date(date.getFullYear(), date.getMonth() + 1, -11).toUTCString(),
      end: new Date(date.getFullYear(), date.getMonth() + 1, -10).toUTCString(),
      allDay: true,
      extendedProps: {
        email: 'test@example.com',
        phone: '0123456789',
        customerName: 'Ahmad',
        calendar: 'Helang - War Room',
      },
    },
  ],
}

// ------------------------------------------------
// GET: Return calendar events
// ------------------------------------------------
mock.onGet('/apps/calendar/events').reply(config => {
  // Get requested calendars as Array
  const calendars = config.params.calendars.split(',')

  return [200, data.events.filter(event => calendars.includes(event.extendedProps.calendar))]
})

// ------------------------------------------------
// POST: Add new event
// ------------------------------------------------
mock.onPost('/apps/calendar/events').reply(config => {
  // Get event from post data
  const { event } = JSON.parse(config.data)

  const { length } = data.events
  let lastIndex = 0
  if (length)
    lastIndex = Number(data.events[length - 1].id)

  event.id = String(lastIndex + 1)

  data.events.push(event)

  return [201, { event }]
})

// ------------------------------------------------
// POST: Update Event
// ------------------------------------------------
mock.onPost(/\/apps\/calendar\/events\/\d+/).reply(config => {
  const { event: eventData } = JSON.parse(config.data)

  const event = data.events.find(e => e.id === eventData.id)
  if (event) {
    Object.assign(event, eventData)

    return [200, { event }]
  }

  return [400, { message: 'Something went wrong' }]
})

// ------------------------------------------------
// DELETE: Remove Event
// ------------------------------------------------
mock.onDelete(/\/apps\/calendar\/events\/\d+/).reply(config => {
  // Get event id from URL
  const eventId = config.url?.substring(config.url.lastIndexOf('/') + 1)

  const eventIndex = data.events.findIndex(e => e.id === eventId)

  data.events.splice(eventIndex, 1)

  return [200]
})
