<template>
  <b-card 
    no-body
    class="p-1"
  >
    <b-card-header class="pb-50 mb-4">
      <h4>
        {{ $t('notification.notifications') }}
      </h4>
    </b-card-header>
    <b-card-body
      class="w-75 align-self-center border rounded mb-5"
      >
      <div
        v-if="notifications.length > 0"
      >
        <app-timeline
          class="p-2"
        >
          
          <app-timeline-item
            v-for="notification in notifications"
            :key="notification.id"
            :variant="notification.data.typeNotification == 'user-message'? 'success' : 'primary'"
            :icon="notification.data.typeNotification == 'user-message'? 'MessageCircleIcon' : 'AlertCircleIcon'"
          >
            <span>
              <div class="d-flex flex-sm-row flex-column flex-wrap justify-content-between mb-1 mb-sm-0">
                <h4>{{ notification.data.title }}</h4>
                <small class="text-muted">{{ notification.timeDiff }}</small>
              </div>
              <p v-html="notification.data.message"></p>
              <b-media>
                <template #aside>
                  <b-avatar :src="'../../'+notification.data.imageUrl" />
                </template>
                <h6>{{notification.data.senderName}}</h6>
                <p v-if="notification.data.typeNotification == 'user-message'">{{ $t('notification.manager') }}</p>
                <p v-if="notification.data.typeNotification == 'system-message'">{{ $t('notification.administrator') }}</p>
              </b-media>
            </span>
            <hr>
          </app-timeline-item>
          
          <!--
          <app-timeline-item
            v-for="notification in notifications"
            :key="notification.id"
            :title="notification.data.title"
            :subtitle="notification.data.message"
            :time="notification.timeDiff"
            :variant="notification.data.typeNotification == 'user-message'? 'success' : 'primary'"
            :icon="notification.data.typeNotification == 'user-message'? 'MessageCircleIcon' : 'AlertCircleIcon'"
          />
          -->
          
          <!--
          <app-timeline-item
            title="Last milestone remain"
            subtitle="You are just one step away from your goal"
            icon="InfoIcon"
            time="3 minutes ago"
            variant="info"
          />

          <app-timeline-item
            title="Your are running low on time"
            subtitle="Only 30 minutes left to finish milestone"
            icon="ClockIcon"
            time="21 minutes ago"
            variant="warning"
          />

          <app-timeline-item
            title="Client Meeting"
            subtitle="New event has been added to your schedule"
            icon="UserIcon"
            time="36 minutes ago"
          />

          <app-timeline-item
            title="Product Design"
            subtitle="Product design added in workflow"
            icon="GridIcon"
            time="1 hour ago"
            variant="success"
          />-->
          
        </app-timeline>
        <div class="text-center">
          <b-button
            variant="outline-primary"
            class="btn-icon rounded-circle"
            @click="fetchNotifications(offset); offset++"
            :hidden="hiddenButtonNotification"
            v-b-tooltip.hover.v-secondary
            :title="$t('notification.showMore')"
          >
            <feather-icon 
              icon="PlusIcon" 
              size="20"
            />
          </b-button>
        </div>
      </div>
      <div
        class="text-center pt-2"
        v-else
      >
        <h6>{{ $t('notification.noNotifications') }}</h6>
      </div>
    </b-card-body>
  </b-card>
</template>

<script>
import {
  BCard, BCardBody, BCardHeader, BMedia, BAvatar, BButton, VBTooltip,
} from 'bootstrap-vue'
import { ref, onUnmounted } from '@vue/composition-api'
import store from '@/store'
import notificationStoreModule from './notificationStoreModule'
import AppTimeline from '@core/components/app-timeline/AppTimeline.vue'
import AppTimelineItem from '@core/components/app-timeline/AppTimelineItem.vue'

export default {
  components: {
    BCard,
    BCardHeader,
    BCardBody,
    BMedia,
    BAvatar,
    BButton,
    
    AppTimeline,
    AppTimelineItem,
  },
  directives: {
    'b-tooltip': VBTooltip,
  },
  data() {
    return {
      offset: 1,
    }
  },
  methods: {
    sumOffset() {
      this.offset += 1;
    },
  },
  setup() {

    const NOTIFICATION_APP_STORE_MODULE_NAME = 'app-notification'

    // Register module
    if (!store.hasModule(NOTIFICATION_APP_STORE_MODULE_NAME)) store.registerModule(NOTIFICATION_APP_STORE_MODULE_NAME, notificationStoreModule)

    // UnRegister on leave
    onUnmounted(() => {
      if (store.hasModule(NOTIFICATION_APP_STORE_MODULE_NAME)) store.unregisterModule(NOTIFICATION_APP_STORE_MODULE_NAME)
    })

    const notifications = ref({})
    //Mostra ou não o botão exibir mais atendimentos
    const hiddenButtonNotification = ref(false)

    //Traz os atendimentos associados a um chat de um contato de pouco em pouco, de acordo com o clique do usuário
    const fetchNotifications = offset => {
      store.dispatch('app-notification/fetchNotifications', { offset: offset } )
        .then(response => {
          //Se existem atendimentos para ser exibidos
          if(response.data.length > 0) {
            if(offset == 0) {
            notifications.value = response.data
            console.log(response.data)
            }
            else {
              //Insere cada novo atendimento carregado no array de serviços
              response.data.map(function(service, key) {
                notifications.value.push(service)
              });
              console.log(response.data)
            }
          }
          else {
            //Esconde o botão que carrega mais atendimentos
            hiddenButtonNotification.value = true
          }
        })
    }

    fetchNotifications(0)

    return {
      notifications,
      hiddenButtonNotification,

      fetchNotifications
    }

  }
}
</script>