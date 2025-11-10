<template>
  <q-layout view="lHh Lpr lFf">
    <!-- HEADER -->
    <q-header class="bg-white text-black" bordered>
      <q-toolbar>
        <q-btn
          flat
          color="primary"
          :icon="leftDrawerOpen ? 'keyboard_double_arrow_left' : 'keyboard_double_arrow_right'"
          aria-label="Menu"
          @click="toggleLeftDrawer"
          unelevated
          dense
        />
        <div class="row items-center q-gutter-sm">
          <!--          <q-badge color="green-8" text-color="white" class="text-bold">EBA</q-badge>-->
          <div class="text-subtitle1 text-weight-medium" style="line-height: 0.9">
            Dashboard de Gestión <br>
            <q-badge color="warning" text-color="black" v-if="roleText" class="text-bold">{{ roleText }}</q-badge>
          </div>
        </div>

        <q-space />

        <div class="row items-center q-gutter-sm">

          <q-btn-dropdown flat unelevated no-caps dropdown-icon="expand_more">
            <template v-slot:label>
              <div class="row items-center no-wrap q-gutter-sm">
                <q-avatar rounded>
                  <q-img :src="`${$url}/../images/${$store.user.avatar}`" width="40px" height="40px" v-if="$store.user.avatar"/>
                  <q-icon name="person" v-else />
                </q-avatar>
                <div class="text-left" style="line-height: 1">
                  <div class="ellipsis" style="max-width: 130px;">
                    {{ $store.user.username }}
                  </div>
                  <!--                  <q-chip dense size="10px" :color="$filters.color($store.user.role)" text-color="white">-->
                  <!--                    {{ $store.user.role }}-->
                  <!--                  </q-chip>-->
                </div>
              </div>
            </template>

            <q-item clickable v-close-popup>
              <q-item-section>
                <q-item-label class="text-grey-7">
                  Permisos asignados
                </q-item-label>
                <q-item-label caption class="q-mt-xs">
                  <div class="row q-col-gutter-xs" style="min-width: 150px; max-width: 150px;">
                    <q-chip
                      v-for="(p, i) in $store.permissions"
                      :key="i"
                      dense
                      color="grey-3"
                      text-color="black"
                      size="12px"
                      class="q-mr-xs q-mb-xs"
                    >
                      {{ p }}
                    </q-chip>
                    <q-badge v-if="!$store.permissions?.length" color="grey-5" outline>Sin permisos</q-badge>
                  </div>
                </q-item-label>
              </q-item-section>
            </q-item>

            <q-separator />

            <q-item clickable v-ripple @click="logout" v-close-popup>
              <q-item-section avatar>
                <q-icon name="logout" />
              </q-item-section>
              <q-item-section>
                <q-item-label>Salir</q-item-label>
              </q-item-section>
            </q-item>
          </q-btn-dropdown>
        </div>
      </q-toolbar>
    </q-header>

    <!-- DRAWER -->
    <q-drawer
      v-model="leftDrawerOpen"
      bordered
      show-if-above
      :width="240"
      :breakpoint="500"
      class="bg-primary text-white"
    >
      <q-list class="q-pb-none">
        <q-item-label header class="text-center q-pa-none q-pt-md">
          <q-avatar size="64px" class="q-mb-sm bg-white" rounded>
            <q-img src="/logo.png" width="90px" />
          </q-avatar>
          <div class="text-weight-bold text-white">EBA</div>
          <div class="text-caption text-white">Sistema de Gestión Apícola</div>
        </q-item-label>

<!--        <q-separator color="green-8" />-->

        <q-item-label header class="q-px-md text-grey-3 q-mt-sm">
          Módulos del Sistema
        </q-item-label>

        <!-- Menú dinámico por permisos -->
        <!--        <template v-for="link in filteredLinks" :key="link.title">-->
        <!--          <q-item-->
        <!--            clickable-->
        <!--            :to="link.link"-->
        <!--            exact-->
        <!--            dense-->
        <!--            class="menu-item"-->
        <!--            active-class="menu-active"-->
        <!--            v-close-popup-->
        <!--          >-->
        <!--            <q-item-section avatar>-->
        <!--              <q-icon-->
        <!--                :name="$route.path === link.link ? 'o_' + link.icon : link.icon"-->
        <!--                :class="$route.path === link.link ? 'text-grey-3' : 'text-white'"-->
        <!--              />-->
        <!--            </q-item-section>-->
        <!--            <q-item-section>-->
        <!--              <q-item-label :class="$route.path === link.link ? 'text-white text-weight-bold' : 'text-white'">-->
        <!--                {{ link.title }}-->
        <!--              </q-item-label>-->
        <!--            </q-item-section>-->
        <!--          </q-item>-->
        <!--        </template>-->
        <q-item dense to="/" exact clickable class="menu-item" active-class="menu-active" v-close-popup>
          <q-item-section avatar>
            <q-icon name="dashboard" class="text-white"/>
          </q-item-section>
          <q-item-section>
            <q-item-label class="text-white">Dashboard</q-item-label>
          </q-item-section>
        </q-item>
        <q-expansion-item dense expand-separator icon="gavel" label="Modulo Producción Primaria" active-class="menu-active" >
          <q-list>
            <q-item :inset-level="0.3" dense to="/productores/crear" clickable class="menu-item" active-class="menu-active" v-close-popup >
              <q-item-section avatar>
                <q-icon name="person_add" class="text-white"/>
              </q-item-section>
              <q-item-section>
                <q-item-label class="text-white">Crear Productor</q-item-label>
              </q-item-section>
            </q-item>
            <q-item :inset-level="0.3" dense to="/productores" clickable class="menu-item" active-class="menu-active" v-close-popup >
              <q-item-section avatar>
                <q-icon name="agriculture" class="text-white"/>
              </q-item-section>
              <q-item-section>
                <q-item-label class="text-white">Gestion de Productores</q-item-label>
              </q-item-section>
            </q-item>
            <q-item :inset-level="0.3" dense to="/geocrud" clickable class="menu-item" active-class="menu-active" v-close-popup >
              <q-item-section avatar>
                <q-icon name="location_city" class="text-white"/>
              </q-item-section>
              <q-item-section>
                <q-item-label class="text-white">Departamento / Provincia </q-item-label>
              </q-item-section>
            </q-item>
            <q-item :inset-level="0.3" dense to="/organizaciones" clickable class="menu-item" active-class="menu-active" v-close-popup >
              <q-item-section avatar>
                <q-icon name="apartment" class="text-white"/>
              </q-item-section>
              <q-item-section>
                <q-item-label class="text-white">Módulo de convenios</q-item-label>
              </q-item-section>
            </q-item>
          </q-list>
        </q-expansion-item>
        <q-expansion-item dense expand-separator icon="inbox" label="Módulo Acopio" active-class="menu-active" >
          <q-list>
            <q-item :inset-level="0.3" dense to="/recoleccion" clickable class="menu-item" active-class="menu-active" v-close-popup >
              <q-item-section avatar>
                <q-icon name="yard" class="text-white"/>
              </q-item-section>
              <q-item-section>
                <q-item-label class="text-white">Recolección</q-item-label>
              </q-item-section>
            </q-item>
            <q-item :inset-level="0.3" dense to="/acopios" clickable class="menu-item" active-class="menu-active" v-close-popup >
              <q-item-section avatar>
                <q-icon name="inbox" class="text-white"/>
              </q-item-section>
              <q-item-section>
                <q-item-label class="text-white">Acopios</q-item-label>
              </q-item-section>
            </q-item>
          </q-list>
        </q-expansion-item>
        <q-item dense to="/usuarios" exact clickable class="menu-item" active-class="menu-active" v-close-popup>
          <q-item-section avatar>
            <q-icon name="people" class="text-white"/>
          </q-item-section>
          <q-item-section>
            <q-item-label class="text-white">Usuarios</q-item-label>
          </q-item-section>
        </q-item>
        <!--        productos cleintes transporte-->
        <q-item dense to="/productos" exact clickable class="menu-item" active-class="menu-active" v-close-popup>
          <q-item-section avatar>
            <q-icon name="inventory" class="text-white"/>
          </q-item-section>
          <q-item-section>
            <q-item-label class="text-white">Productos</q-item-label>
          </q-item-section>
        </q-item>
        <q-item dense to="/clientes" exact clickable class="menu-item" active-class="menu-active" v-close-popup>
          <q-item-section avatar>
            <q-icon name="storefront" class="text-white"/>
          </q-item-section>
          <q-item-section>
            <q-item-label class="text-white">Clientes</q-item-label>
          </q-item-section>
        </q-item>
        <q-item dense to="/transportes" exact clickable class="menu-item" active-class="menu-active" v-close-popup>
          <q-item-section avatar>
            <q-icon name="local_shipping" class="text-white"/>
          </q-item-section>
          <q-item-section>
            <q-item-label class="text-white">Transporte</q-item-label>
          </q-item-section>
        </q-item>
        <!--        plantas-->
        <q-item dense to="/plantas" exact clickable class="menu-item" active-class="menu-active" v-close-popup>
          <q-item-section avatar>
            <q-icon name="factory" class="text-white"/>
          </q-item-section>
          <q-item-section>
            <q-item-label class="text-white">Plantas de Procesamiento</q-item-label>
          </q-item-section>
        </q-item>
        <!--        COMERZILIZACION-->
        <q-expansion-item dense expand-separator icon="store" label="Módulo Comercialización" active-class="menu-active" >
          <q-list>
            <!--            crear ventas-->
            <q-item :inset-level="0.3" dense to="/ventas/crear" clickable class="menu-item" active-class="menu-active" v-close-popup >
              <q-item-section avatar>
                <q-icon name="point_of_sale" class="text-white"/>
              </q-item-section>
              <q-item-section>
                <q-item-label class="text-white">Crear Venta</q-item-label>
              </q-item-section>
            </q-item>
            <!--            gestionar ventas-->
            <q-item :inset-level="0.3" dense to="/ventas" clickable class="menu-item" active-class="menu-active" v-close-popup >
              <q-item-section avatar>
                <q-icon name="sell" class="text-white"/>
              </q-item-section>
              <q-item-section>
                <q-item-label class="text-white">Gestionar Ventas</q-item-label>
              </q-item-section>
            </q-item>
          </q-list>
        </q-expansion-item>

        <div class="q-pa-md">
          <div class="text-white-7 text-caption">
            EBA v{{ $version }}
          </div>
          <div class="text-white-7 text-caption">
            © {{ new Date().getFullYear() }} Sistema de Gestión Apícola
          </div>
        </div>

        <q-item clickable class="text-white" @click="logout" v-close-popup>
          <q-item-section avatar>
            <q-icon name="logout" />
          </q-item-section>
          <q-item-section>
            <q-item-label>Salir</q-item-label>
          </q-item-section>
        </q-item>
      </q-list>
    </q-drawer>

    <!-- PAGE -->
    <q-page-container class="bg-grey-2">
      <router-view />
    </q-page-container>
  </q-layout>
</template>

<script setup>
import { computed, getCurrentInstance, ref } from 'vue'
import { useCounterStore } from 'stores/example-store'

const { proxy } = getCurrentInstance()
const store = useCounterStore()

const leftDrawerOpen = ref(false)

// Helpers de permisos
function hasPerm (perm) {
  if (!perm) return true
  return store.permissions?.includes(perm)
}
function hasAnyPerm (perms = []) {
  return perms.some(p => hasPerm(p))
}

const linksList = [
  { title: 'Dashboard',            icon: 'dashboard',     link: '/',                   canPerm: 'Dashboard' },
  { title: 'Geografía',          icon: 'location_city', link: '/geocrud',  canPerm: 'Geografia' },
  { title: 'Organizaciones',       icon: 'apartment',    link: '/organizaciones',     canPerm: 'Organizaciones' },
  // { path: '/productores/crear', component: () => import('pages/productores/ProductorCrear.vue'), meta: { requiresAuth: true, perm: 'Productores' } },
  { title: 'Productores Crear',   icon: 'person_add',  link: '/productores/crear', canPerm: 'Productores Crear' },
  { title: 'Productores / Agricultores', icon: 'agriculture', link: '/productores', canPerm: 'Productores / Agricultores' },
  // { title: 'Recolección',          icon: 'yard',          link: '/recoleccion',        canPerm: 'Recoleccion' },
  // { title: 'Procesamiento',        icon: 'precision_manufacturing', link: '/procesamiento', canPerm: 'Procesamiento' },
  // { title: 'Almacenamiento',       icon: 'warehouse',     link: '/almacenamiento',     canPerm: 'Almacenamiento' },
  // { title: 'Despacho',             icon: 'local_shipping',link: '/despacho',           canPerm: 'Despacho' },
  { title: 'Acopios',              icon: 'inbox',         link: '/acopios',            canPerm: 'Acopios' },
  { title: 'Usuarios',             icon: 'people',        link: '/usuarios',           canPerm: 'Usuarios' },
  // { title: 'Reportes',             icon: 'print',    link: '/reportes',           canPerm: 'Reportes' },
  { title: 'Configuración',        icon: 'settings',      link: '/configuraciones',    canPerm: 'Configuracion' },
  { title: 'Soporte',              icon: 'support',       link: '/soporte',            canPerm: 'Soporte' },
]

const filteredLinks = computed(() => {
  return linksList.filter(link => {
    if (Array.isArray(link.canPerm)) return hasAnyPerm(link.canPerm)
    if (link.canPerm) return hasPerm(link.canPerm)
    return true
  })
})

function toggleLeftDrawer () {
  leftDrawerOpen.value = !leftDrawerOpen.value
}

function logout () {
  proxy.$alert.dialog('¿Desea salir del sistema?')
    .onOk(() => {
      proxy.$axios.post('/logout')
        .then(() => {
          proxy.$store.isLogged = false
          proxy.$store.user = {}
          proxy.$store.permissions = []
          localStorage.removeItem('tokenEBA')
          proxy.$router.push('/login')
        })
        .catch(() => proxy.$alert.error('Error al cerrar sesión. Intente nuevamente.'))
    })
}

const roleText = computed(() => {
  const role = proxy.$store.user.role
  if (!role) return ''
  if (role === 'Administrador') return 'Administrador'
  return role
})
</script>

<style scoped>
.menu-item {
  border-radius: 10px;
  margin: 4px 8px;
  padding: 4px 6px;
}
.menu-active {
  background: rgba(255, 255, 255, 0.15);
  color: #fff !important;
  border-radius: 10px;
}
</style>
