const routes = [
  {
    path: '/',
    component: () => import('layouts/MainLayout.vue'),
    children: [
      { path: '', component: () => import('pages/IndexPage.vue'), meta: { requiresAuth: true } },
      { path: 'usuarios', component: () => import('pages/usuarios/Usuarios.vue'), meta: { requiresAuth: true } },
      { path: 'insumos', component: () => import('pages/insumos/Insumos.vue'), meta: { requiresAuth: true } },
      // { path: 'compras-insumos', component: () => import('pages/compras/ComprasIndex.vue'), meta: { requiresAuth: true } },
      {
        path: '/compras',
        component: () => import('pages/compras/ComprasIndex.vue'),
        meta: { requiresAuth: true }
      },
      {
        path: '/compras/insumos',
        component: () => import('pages/compras/InsumosResumen.vue'),
        meta: { requiresAuth: true }
      },
      // productos
      {
        path: '/productos',
        component: () => import('pages/productos/Productos.vue'),
        meta: { requiresAuth: true }
      },
      // ventas
      {
        path: '/ventas',
        component: () => import('pages/ventas/Ventas.vue'),
        meta: { requiresAuth: true }
      },
      // /ventas/lista
      {
        path: '/ventas/lista',
        component: () => import('pages/ventas/VentasLista.vue'),
        meta: { requiresAuth: true }
      },
      // reportes
      {
        path: '/reportes',
        component: () => import('pages/reportes/Reportes.vue'),
        meta: { requiresAuth: true }
      },
      {
        path: '/almacenes',
        component: () => import('pages/almacen/Almacenes.vue')
      },
      {
        path: '/compras-almacen',
        component: () => import('pages/almacen/ComprasAmacen.vue')
      },
      {
        path: '/compras-almacen/nueva',
        component: () => import('pages/almacen/ComprasAlmacenForm.vue')
      }
    ]
  },
  {
    path: '/login',
    component: () => import('layouts/Login.vue')
  },
  // Always leave this as last one,
  // but you can also remove it
  {
    path: '/:catchAll(.*)*',
    component: () => import('pages/ErrorNotFound.vue')
  }
]

export default routes
