import mock from './mock'

// Apps
import './apps/calendar'

// forwards the matched request over network
mock.onAny().passThrough()
