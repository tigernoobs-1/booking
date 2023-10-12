import { CalendarEvent } from '@/@fake-db/types';
import type { Except } from 'type-fest';

export interface Event extends CalendarEvent {
  extendedProps: {
    customerName: string
    email: string
    phone: string
    notes: string
  }
}

export type NewEvent = Except<Event, 'id'>
