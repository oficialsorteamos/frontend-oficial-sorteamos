<template>
  <div class="sidebar-left">
    <div class="sidebar">
      <div class="sidebar-content todo-sidebar">
        <div class="todo-app-menu">
          <div class="add-task">
            <b-button
              variant="dark"
              class="btn-icon rounded-circle mb-1"
              v-b-tooltip.hover.v-secondary
              :title="$t('user.back')"
              :to="{ name: 'apps-chatbot' }"
            >
              <feather-icon 
                icon="ArrowLeftIcon" 
                size="16"
              />
            </b-button>
            <b-button
              v-ripple.400="'rgba(255, 255, 255, 0.15)'"
              variant="primary"
              block
              @click="$emit('update:is-task-handler-sidebar-active', true); $emit('close-left-sidebar'); $emit('set-first-bloc')"
            >
              {{ $t('chatbot.todoLeftSidebar.addBloc') }}
            </b-button>
          </div>
          <vue-perfect-scrollbar
            :settings="perfectScrollbarSettings"
            class="sidebar-menu-list scroll-area"
          >
            <!-- Filters 
            <b-list-group class="list-group-filters">
              <b-list-group-item
                v-for="filter in taskFilters"
                :key="filter.title + $route.path"
                :to="filter.route"
                :active="isDynamicRouteActive(filter.route)"
                @click="$emit('close-left-sidebar')"
              >
                <feather-icon
                  :icon="filter.icon"
                  size="18"
                  class="mr-75"
                />
                <span class="align-text-bottom line-height-1">{{ filter.title }}</span>
              </b-list-group-item>
            </b-list-group>
            -->
            <!-- Actions -->
            <div class="mt-3 px-2 d-flex justify-content-between">
              <h6 class="section-label mb-1">
                {{ $t('chatbot.todoLeftSidebar.legend') }}
              </h6>
              <!--
              <feather-icon icon="PlusIcon" />
              -->
            </div>

            <b-list-group class="list-group-labels">
              <b-list-group-item
                v-for="tag in taskTags"
                :key="tag.title + $route.path"
              >
                <span
                  class="bullet bullet-sm mr-1"
                  :class="`bullet-${tag.color}`"
                />
                <span>{{ tag.title }}</span>
              </b-list-group-item>
            </b-list-group>

          </vue-perfect-scrollbar>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import VuePerfectScrollbar from 'vue-perfect-scrollbar'
import { BButton, BListGroup, BListGroupItem, VBTooltip, } from 'bootstrap-vue'
import { isDynamicRouteActive } from '@core/utils/utils'
import Ripple from 'vue-ripple-directive'

export default {
  components: {
    BButton,
    VBTooltip,
    BListGroup,
    BListGroupItem,
    VuePerfectScrollbar,
  },
  props: {
    taskTags: {
      type: Array,
      required: true,
    },
  },
  directives: {
    Ripple,
    'b-tooltip': VBTooltip,
  },
  setup() {
    const perfectScrollbarSettings = {
      maxScrollbarLength: 60,
    }

    const taskFilters = [
      { title: 'My Task', icon: 'MailIcon', route: { name: 'apps-chatbot' } },
      { title: 'Important', icon: 'StarIcon', route: { name: 'apps-chatbot-filter', params: { filter: 'important' } } },
      { title: 'Completed', icon: 'CheckIcon', route: { name: 'apps-chatbot-filter', params: { filter: 'completed' } } },
      { title: 'Deleted', icon: 'TrashIcon', route: { name: 'apps-chatbot-filter', params: { filter: 'deleted' } } },
    ]

    return {
      perfectScrollbarSettings,
      taskFilters,
      isDynamicRouteActive,
    }
  },
}
</script>

<style>

</style>
