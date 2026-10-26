<template>
  <q-layout view="lHh Lpr lFf">
    <!-- ================= HEADER ================= -->
    <q-header class="app-header" bordered>
      <q-toolbar class="q-px-md" style="min-height: 52px">
        <q-btn
          flat
          dense
          round
          class="text-grey-8"
          :icon="drawerCollapsed ? 'keyboard_double_arrow_right' : 'keyboard_double_arrow_left'"
          aria-label="Menu"
          @click="toggleLeftDrawer"
        >
          <q-tooltip anchor="bottom middle" self="top middle">
            {{ drawerCollapsed ? 'Expandir menú' : 'Contraer menú' }}
          </q-tooltip>
        </q-btn>

        <div class="q-ml-sm">
          <div class="text-subtitle1 text-weight-bold text-grey-9" style="line-height: 1.1">
            {{ currentTitle }}
          </div>
          <div class="text-caption text-grey-6" style="line-height: 1.1">
            {{ currentSubtitle }}
          </div>
        </div>

        <q-space />

        <q-btn-dropdown
          flat
          unelevated
          no-caps
          dropdown-icon="expand_more"
          class="user-chip"
        >
          <template v-slot:label>
            <div class="row items-center no-wrap q-gutter-sm">
              <q-avatar size="32px" rounded class="bg-grey-3 text-primary">
                <q-img
                  v-if="$store.user.avatar"
                  :src="`${$url}/../images/${$store.user.avatar}`"
                  width="32px"
                  height="32px"
                />
                <q-icon v-else name="person" size="18px" />
              </q-avatar>
              <div class="text-left gt-xs" style="line-height: 1.15">
                <div class="text-weight-medium text-grey-9 ellipsis" style="max-width: 140px">
                  {{ $store.user.username }}
                </div>
                <div class="text-caption text-grey-6 ellipsis" style="max-width: 140px">
                  {{ $store.user.role || 'Usuario' }}
                </div>
              </div>
            </div>
          </template>

          <div class="q-pa-md" style="min-width: 230px; max-width: 260px">
            <div class="text-caption text-grey-6 text-uppercase text-weight-medium q-mb-xs">
              Permisos asignados
            </div>
            <div class="row">
              <q-chip
                v-for="(p, i) in $store.permissions"
                :key="i"
                dense
                square
                color="grey-2"
                text-color="grey-9"
                size="11px"
                class="q-mr-xs q-mb-xs"
              >
                {{ p }}
              </q-chip>
              <q-badge v-if="!$store.permissions?.length" color="grey-5" outline>Sin permisos</q-badge>
            </div>
          </div>

          <q-separator />

          <q-list dense>
            <q-item clickable v-ripple @click="logout" v-close-popup class="text-negative">
              <q-item-section avatar style="min-width: 34px">
                <q-icon name="logout" size="20px" />
              </q-item-section>
              <q-item-section>
                <q-item-label class="text-weight-medium">Cerrar sesión</q-item-label>
              </q-item-section>
            </q-item>
          </q-list>
        </q-btn-dropdown>
      </q-toolbar>
    </q-header>

    <!-- ================= DRAWER ================= -->
    <q-drawer
      v-model="leftDrawerOpen"
      show-if-above
      :width="224"
      :mini="miniState"
      :mini-width="60"
      :breakpoint="1023"
      class="app-drawer"
    >
      <div class="column full-height drawer-body">
        <!-- BRAND -->
        <div class="brand-block" :class="{ 'brand-mini': miniState }">
          <q-avatar square size="34px" class="brand-logo">
            <q-img src="/logo.png" width="28px" fit="contain" />
          </q-avatar>
          <div v-if="!miniState" class="brand-text">
            <div class="brand-name">Chicken’s</div>
            <div class="brand-sub">Garden · Punto de venta</div>
          </div>
        </div>

        <!-- MENU -->
        <q-scroll-area class="col nav-scroll">
          <q-list class="q-py-xs">
            <template v-for="(group, gi) in visibleMenu" :key="gi">
              <div v-if="!miniState" class="nav-section">{{ group.section }}</div>
              <q-separator v-else class="q-my-xs nav-sep" />

              <template v-for="item in group.items" :key="item.label">
                <!-- item simple -->
                <q-item
                  v-if="!item.children"
                  clickable
                  v-ripple
                  :to="item.to"
                  exact
                  class="nav-item"
                  active-class="nav-item--active"
                >
                  <q-item-section avatar class="nav-avatar">
                    <div class="nav-icon">
                      <q-icon :name="item.icon" size="16px" />
                    </div>
                  </q-item-section>
                  <q-item-section v-if="!miniState">
                    <q-item-label class="nav-label">{{ item.label }}</q-item-label>
                  </q-item-section>
                  <q-tooltip v-if="miniState" anchor="center right" self="center left">
                    {{ item.label }}
                  </q-tooltip>
                </q-item>

                <!-- grupo desplegable -->
                <q-expansion-item
                  v-else-if="!miniState"
                  :icon="item.icon"
                  :label="item.label"
                  :default-opened="isGroupActive(item)"
                  class="nav-expansion"
                  header-class="nav-item nav-expansion__header"
                  expand-icon-class="nav-expand-icon"
                >
                  <q-item
                    v-for="child in item.children"
                    :key="child.to"
                    clickable
                    v-ripple
                    :to="child.to"
                    exact
                    class="nav-item nav-item--child"
                    active-class="nav-item--active"
                  >
                    <q-item-section avatar class="nav-avatar">
                      <div class="nav-icon nav-icon--sm">
                        <q-icon :name="child.icon" size="15px" />
                      </div>
                    </q-item-section>
                    <q-item-section>
                      <q-item-label class="nav-label">{{ child.label }}</q-item-label>
                    </q-item-section>
                  </q-item>
                </q-expansion-item>

                <!-- en modo mini los hijos se muestran planos -->
                <template v-else>
                  <q-item
                    v-for="child in item.children"
                    :key="child.to"
                    clickable
                    v-ripple
                    :to="child.to"
                    exact
                    class="nav-item"
                    active-class="nav-item--active"
                  >
                    <q-item-section avatar class="nav-avatar">
                      <div class="nav-icon">
                        <q-icon :name="child.icon" size="16px" />
                      </div>
                    </q-item-section>
                    <q-tooltip anchor="center right" self="center left">
                      {{ child.label }}
                    </q-tooltip>
                  </q-item>
                </template>
              </template>
            </template>
          </q-list>
        </q-scroll-area>

        <!-- FOOTER -->
        <div class="nav-footer">
          <template v-if="!miniState">
            <div class="nav-version">Chicken’s Garden v{{ $version }}</div>
            <div class="nav-version">© {{ new Date().getFullYear() }} Todos los derechos reservados</div>
          </template>
          <q-btn
            unelevated
            no-caps
            class="logout-btn q-mt-sm"
            :class="{ 'logout-btn--mini': miniState }"
            icon="logout"
            :label="miniState ? '' : 'Salir'"
            @click="logout"
          >
            <q-tooltip v-if="miniState" anchor="center right" self="center left">Salir</q-tooltip>
          </q-btn>
        </div>
      </div>
    </q-drawer>

    <!-- ================= PAGE ================= -->
    <q-page-container class="bg-grey-2">
      <router-view />
    </q-page-container>
  </q-layout>
</template>

<script setup>
import { computed, getCurrentInstance, ref } from 'vue'
import { useRoute } from 'vue-router'
import { useQuasar } from 'quasar'
import { useCounterStore } from 'stores/example-store'

const { proxy } = getCurrentInstance()
const store = useCounterStore()
const route = useRoute()
const $q = useQuasar()

const leftDrawerOpen = ref(false)
const drawerCollapsed = ref(false)

// en pantallas grandes el botón contrae el menú (modo mini); en móvil lo oculta
const miniState = computed(() => drawerCollapsed.value && $q.screen.gt.sm)

function toggleLeftDrawer () {
  if ($q.screen.gt.sm) {
    drawerCollapsed.value = !drawerCollapsed.value
    leftDrawerOpen.value = true
  } else {
    leftDrawerOpen.value = !leftDrawerOpen.value
  }
}

function canPermission (permission) {
  // acepta un permiso o una lista (basta con tener uno)
  const perms = Array.isArray(permission) ? permission : [permission]
  return perms.some(p => store.permissions.includes(p))
}

// ---------- Definición del menú ----------
const menu = [
  {
    section: 'General',
    items: [
      { label: 'Inicio', icon: 'space_dashboard', to: '/' }
    ]
  },
  {
    section: 'Operaciones',
    items: [
      { label: 'Nueva venta', icon: 'point_of_sale', to: '/ventas', perm: 'Ventas' },
      { label: 'Ventas', icon: 'receipt_long', to: '/ventas/lista', perm: 'Ventas' },
      { label: 'Inventario de caja', icon: 'local_drink', to: '/ventas/inventario-caja', perm: 'Ventas' },
      {
        label: 'Compras',
        icon: 'shopping_cart',
        perm: 'Compras',
        children: [
          { label: 'Compras de insumos', icon: 'add_shopping_cart', to: '/compras/insumos' },
          { label: 'Gestión de compras', icon: 'inventory', to: '/compras' }
        ]
      },
      { label: 'Despachos de pollo', icon: 'local_shipping', to: '/despachos', perm: ['Despachos', 'Despachos admin'] }
    ]
  },
  {
    section: 'Catálogo',
    items: [
      { label: 'Productos', icon: 'shopping_bag', to: '/productos', perm: 'Productos' },
      { label: 'Insumos', icon: 'inventory_2', to: '/insumos', perm: 'Insumos' },
      { label: 'Control de consumo', icon: 'fact_check', to: '/insumos/control-consumo', perm: 'Insumos' },
      { label: 'Control consumo (tabla)', icon: 'table_view', to: '/insumos/control-consumo-tabla', perm: 'Insumos' },
      { label: 'Productos de despacho', icon: 'egg', to: '/despachos/productos', perm: 'Despachos admin' }
    ]
  },
  {
    section: 'Reportes',
    items: [
      { label: 'Reportes', icon: 'bar_chart', to: '/reportes', perm: 'Reportes' },
      { label: 'Reportes del sistema', icon: 'analytics', to: '/reportes-sistema', perm: 'Reportes' },
      { label: 'Resumen de ventas', icon: 'summarize', to: '/ventas-resumen', perm: 'Reportes' },
      { label: 'Ajuste de caja', icon: 'account_balance_wallet', to: '/reportes/ajuste-caja-garden', perm: 'Reportes' }
    ]
  },
  {
    section: 'Administración',
    items: [
      { label: 'Usuarios', icon: 'group', to: '/usuarios', perm: 'Usuarios' }
    ]
  }
]

const visibleMenu = computed(() =>
  menu
    .map(g => ({ ...g, items: g.items.filter(i => !i.perm || canPermission(i.perm)) }))
    .filter(g => g.items.length)
)

function isGroupActive (item) {
  return item.children?.some(c => route.path === c.to) ?? false
}

// ---------- Título de la página actual ----------
const titles = {
  '/': ['Inicio', 'Resumen general del sistema'],
  '/ventas': ['Nueva venta', 'Registro de venta en punto de caja'],
  '/ventas/lista': ['Ventas', 'Resumen e historial de ventas'],
  '/ventas/inventario-caja': ['Inventario de caja', 'Refrescos del vendedor: inicial, aumentos y vendido'],
  '/compras': ['Compras', 'Gestión de compras a proveedores'],
  '/compras/insumos': ['Compras de insumos', 'Detalle de insumos comprados'],
  '/productos': ['Productos', 'Catálogo de productos de venta'],
  '/insumos': ['Insumos', 'Materia prima y control de stock'],
  '/insumos/control-consumo': ['Control de consumo', 'Consumo semanal de insumos controlados'],
  '/insumos/control-consumo-tabla': ['Insumos contables', 'Compras por producto: kilos, precio y relación'],
  '/reportes': ['Reportes', 'Indicadores del negocio'],
  '/reportes-sistema': ['Reportes del sistema', 'Auditoría y actividad'],
  '/ventas-resumen': ['Resumen de ventas', 'Consolidado por periodo'],
  '/reportes/ajuste-caja-garden': ['Ajuste de caja', 'Ingresos y egresos manuales'],
  '/usuarios': ['Usuarios', 'Cuentas, roles y permisos'],
  '/despachos': ['Despachos de pollo', 'Recepción y control de peso de las notas de despacho'],
  '/despachos/productos': ['Productos de despacho', 'Productos que llegan en las notas del proveedor']
}

const currentTitle = computed(() => titles[route.path]?.[0] ?? 'Chicken’s Garden')
const currentSubtitle = computed(() => titles[route.path]?.[1] ?? 'Sistema de gestión')

function logout () {
  proxy.$alert.dialog('¿Desea salir del sistema?')
    .onOk(() => {
      proxy.$axios.post('/logout')
        .then(() => {
          proxy.$store.isLogged = false
          proxy.$store.user = {}
          proxy.$store.permissions = []
          localStorage.removeItem('tokenChicken')
          proxy.$router.push('/login')
        })
        .catch(() => proxy.$alert.error('Error al cerrar sesión. Intente nuevamente.'))
    })
}
</script>

<style scoped lang="scss">
/* ---------- HEADER ---------- */
.app-header {
  background: #fff;
  color: #1f2937;
  box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
}
.user-chip {
  border: 1px solid #e5e7eb;
  border-radius: 999px;
  padding: 2px 6px 2px 4px;
}

/* ---------- DRAWER ---------- */
/* QDrawer aplica la clase al contenedor externo, por eso el fondo va
   tanto en el aside interno como en el contenido propio del drawer */
.app-drawer :deep(.q-drawer) {
  background: #c2431c;
  border-right: 1px solid rgba(0, 0, 0, 0.12);
}
.drawer-body {
  background: linear-gradient(180deg, #e2542f 0%, #c2431c 55%, #a33314 100%);
  color: #fff;
}
.drawer-body :deep(.q-scrollarea__thumb) {
  background: rgba(255, 255, 255, 0.45);
}

.brand-block {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 8px 10px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.18);
}
.brand-block.brand-mini {
  justify-content: center;
  padding: 8px 0;
}
.brand-logo {
  background: #fff;
  border-radius: 8px;
  flex: 0 0 auto;
}
.brand-text {
  min-width: 0;
}
.brand-name {
  font-size: 14px;
  font-weight: 700;
  line-height: 1.1;
  letter-spacing: 0.2px;
}
.brand-sub {
  font-size: 10px;
  line-height: 1.15;
  color: rgba(255, 255, 255, 0.72);
  white-space: nowrap;
}

.nav-scroll {
  min-height: 0;
}
.nav-section {
  font-size: 9px;
  font-weight: 700;
  letter-spacing: 1.1px;
  text-transform: uppercase;
  color: rgba(255, 255, 255, 0.62);
  padding: 9px 12px 2px;
}
.nav-sep {
  background: rgba(255, 255, 255, 0.2);
}

:deep(.nav-item) {
  position: relative;
  min-height: 30px;
  margin: 1px 6px;
  padding: 2px 8px;
  border-radius: 8px;
  color: rgba(255, 255, 255, 0.92);
  transition: background-color 0.16s ease, color 0.16s ease;
}
:deep(.nav-item:hover) {
  background: rgba(255, 255, 255, 0.16);
  color: #fff;
}
:deep(.nav-item--active) {
  background: #fff;
  color: #a33314;
  font-weight: 600;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.18);
}
:deep(.nav-item--active .nav-icon) {
  background: rgba(226, 84, 47, 0.14);
}
:deep(.nav-item--child) {
  margin-left: 16px;
  min-height: 28px;
}
:deep(.nav-avatar) {
  min-width: 28px;
  padding-right: 6px;
}
.nav-icon {
  width: 22px;
  height: 22px;
  border-radius: 6px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: rgba(255, 255, 255, 0.16);
}
.nav-icon--sm {
  width: 20px;
  height: 20px;
  border-radius: 6px;
}
.nav-label {
  font-size: 12px;
  font-weight: 500;
  letter-spacing: 0.1px;
}

:deep(.nav-expansion__header) {
  color: rgba(255, 255, 255, 0.92);
  font-size: 12px;
  font-weight: 500;
}
:deep(.nav-expansion__header .q-item__section--avatar) {
  min-width: 28px;
  padding-right: 6px;
}
:deep(.nav-expand-icon) {
  color: rgba(255, 255, 255, 0.7);
}

/* ---------- FOOTER ---------- */
.nav-footer {
  padding: 8px 10px 10px;
  border-top: 1px solid rgba(255, 255, 255, 0.18);
}
.nav-version {
  color: rgba(255, 255, 255, 0.62);
  font-size: 10px;
  line-height: 1.35;
}
.logout-btn {
  width: 100%;
  border-radius: 8px;
  background: rgba(0, 0, 0, 0.2);
  color: #fff;
  font-size: 12px;
  font-weight: 600;
  border: 1px solid rgba(255, 255, 255, 0.28);
}
.logout-btn:hover {
  background: rgba(0, 0, 0, 0.34);
}
.logout-btn--mini {
  min-width: 36px;
  padding: 4px;
}
</style>
