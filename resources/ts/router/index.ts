import { canNavigate } from '@layouts/plugins/casl'
import { setupLayouts } from 'virtual:generated-layouts'
import { createRouter, createWebHistory } from 'vue-router'
import routes from '~pages'
import { isUserLoggedIn } from './utils'



const router = createRouter({
  history: createWebHistory('/'),
  
  routes: [...setupLayouts(routes)],
})

router.beforeEach(to => {
  const isLoggedIn = isUserLoggedIn()
  if (to.name === 'admin') {
    if ((isLoggedIn && !canNavigate(to)) || !isLoggedIn) {
      return { name: 'not-authorized' }
    }
  }
  if (!isLoggedIn && to.name === 'booking')
    return { name: 'login' }
  if (!isLoggedIn && to.name === 'complaint')
    return { name: 'login'}
  if (isLoggedIn && to.name === 'login')
    return { name: 'index' }
})

/* router.beforeEach(to => {
  const isLoggedIn = isUserLoggedIn()

  if (canNavigate(to)) {
    if (to.meta.redirectIfLoggedIn && isLoggedIn)
      return '/'
  }
  else {
    if (isLoggedIn)
      return { name: 'login', query: { to: to.name !== 'index' ? to.fullPath : undefined } }
  }
}) */
// Docs: https://router.vuejs.org/guide/advanced/navigation-guards.html#global-before-guards

export default router
