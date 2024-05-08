<template>
  <b-card :title="$t('contacts.contactViewUserTimelineCard.servicesTimeline')">
    <app-timeline>
      <!-- Existe algum atendimento para o contato -->
      <div
        v-if="servicesData.length > 0"
      >
        <app-timeline-item 
          v-for="service in servicesData"
          :key="service.id"
          :variant="service.type_status_service_id == 1 ? 'success' : 'primary'"
        >
          <div class="d-flex flex-sm-row flex-column flex-wrap justify-content-between mb-1 mb-sm-0">
            <h6>{{ $t('contacts.contactViewUserTimelineCard.protocol') }} Nº <b>{{ service.ser_protocol_number }}</b></h6>
            <small class="text-muted">{{ service.timeDiff }}</small>
          </div>
          <p class="align-middle">
            <feather-icon
              icon="ClockIcon"
              size="14"
              class="mr-10"
            />
            {{ $t('contacts.contactViewUserTimelineCard.opennedOn') }} {{ formatDateTimeOnlyNumber(service.created_at) }}
          </p>
          <p class="align-middle" 
            v-for="action in service.actions"
            :key="action.id"
          >
            <!-- Se o for uma transferência de atendimento de um departamento para outro departamento/Operador -->
            <span
              v-if="action.user_sender"
            >
            <feather-icon
              icon="RepeatIcon"
              size="14"
              class="mr-10"      
            />
              <!-- Se foi transferido por um OPERADOR -->
              <span
                v-if="action.department_sender"
              >
                {{ $t('contacts.contactViewUserTimelineCard.transferredOperator') }} <strong> {{action.user_sender.name}} </strong> {{ $t('contacts.contactViewUserTimelineCard.fromThe') }} <strong>{{action.department_sender.dep_name}}</strong> {{ $t('contacts.contactViewUserTimelineCard.departmentToThe') }} <strong>{{action.department_receive.dep_name}}</strong> {{ $t('contacts.contactViewUserTimelineCard.department') }}
              </span>
              <!-- Se foi transferido por um GESTOR -->
              <span
                v-else
              >
                {{ $t('contacts.contactViewUserTimelineCard.transferredManager') }} <strong> {{action.user_sender.name}} </strong> {{ $t('contacts.contactViewUserTimelineCard.toThe') }} <strong>{{action.department_receive.dep_name}}</strong> {{ $t('contacts.contactViewUserTimelineCard.department') }}
              </span>
              
            </span>
            <!-- Se for um encaminhamento de atendimento de chatbot para um departamento/Operador -->
            <span
              v-else
            >
              <feather-icon
                icon="ArrowRightCircleIcon"
                size="14"
                class="mr-10"
              />
                {{ $t('contacts.contactViewUserTimelineCard.forwardedServiceOn') }} {{ formatDateTimeOnlyNumber(action.created_at) }}

              <!-- Caso o operador tenha enviado alguma mensagem para o contato após o chatbot ter encaminhado o atendimento-->
              <p class="align-middle mt-1"
                v-if="service.startComunicationOperator"
              >
                <feather-icon
                  icon="MessageSquareIcon"
                  size="14"
                  class="mr-10"
                />
                  {{ $t('contacts.contactViewUserTimelineCard.communicationInitiatedOperator') }} <strong> {{ service.startComunicationOperator.user_sender.name }} </strong> {{ service.startComunicationOperator.user_sender.at }} {{ formatDateTimeOnlyNumber(service.startComunicationOperator.created_at) }} 
              </p>
            </span>
          </p>
          <p v-if="service.ser_dt_end_service">
            <feather-icon
              icon="PowerIcon"
              size="14"
              class="mr-10"
            />
              <span
                v-if="service.user_end_service" 
              >
                {{ $t('contacts.contactViewUserTimelineCard.completedOn') }} {{ formatDateTimeOnlyNumber(service.ser_dt_end_service) }} {{$t('contacts.contactViewUserTimelineCard.by')}} <strong>{{ service.user_end_service.name }}</strong>
              </span>
              <span
                v-else
              >
                {{ $t('contacts.contactViewUserTimelineCard.completedOn') }} {{ formatDateTimeOnlyNumber(service.ser_dt_end_service) }}
              </span>
          </p>
          <!-- Exibe a nota atribuída ao atendimento, se houver -->
          <p v-if="service.evaluation"
            style="margin-top: -5px"
          >
            <feather-icon
              icon="StarIcon"
              size="14"
              class="mr-10"
            />
            <b-form-rating
              id="rating-sm-no-border"
              v-model="service.evaluation.ser_rating"
              no-border
              variant="warning"
              inline
              size="sm"
              :readonly="true"
              v-b-tooltip.hover.v-secondary
              :title="service.evaluation.ser_rating"
            />
          </p>
          
        </app-timeline-item>
      </div>
      <div
        v-else
      >
        <h5 
          class="mt-5"
          style="text-align: center"
        >
          {{ $t('contacts.contactViewUserTimelineCard.noServiceFound') }}
        </h5>
      </div>
      <!--
      <app-timeline-item variant="warning">

        <div class="d-flex flex-sm-row flex-column flex-wrap justify-content-between mb-1 mb-sm-0">
          <h6>Client Meeting</h6>
          <small class="text-muted">45 min ago</small>
        </div>
        <p>Project meeting with john @10:15am.</p>
        <b-media>
          <template #aside>
            <b-avatar :src="require('@/assets/images/avatars/8-small.png')" />
          </template>
          <h6>John Doe (Client)</h6>
          <p class="mb-0">
            CEO of Infibeam
          </p>
        </b-media>
      </app-timeline-item>
      <app-timeline-item
        variant="info"
        title="Create a new project for client"
        subtitle="Add files to new design folder"
        time="2 days ago"
      />
      -->
      <div class="text-center">
        <b-button
          variant="outline-primary"
          class="btn-icon rounded-circle"
          @click="$emit('load-services', offset); sumOffset();"
          :hidden="hiddenButtonService"
          v-b-tooltip.hover.v-secondary
          :title="$t('contacts.contactViewUserTimelineCard.showMore')"
        >
          <feather-icon 
            icon="PlusIcon" 
            size="20"
          />
        </b-button>
      </div>
    </app-timeline>
  </b-card>
</template>

<script>
import {
  BCard, BImg, BMedia, BAvatar, BFormRating, VBTooltip, BButton,
} from 'bootstrap-vue'
import AppTimeline from '@core/components/app-timeline/AppTimeline.vue'
import AppTimelineItem from '@core/components/app-timeline/AppTimelineItem.vue'
import { formatDateTimeOnlyNumber } from '@core/utils/filter'

export default {
  components: {
    BCard,
    BImg,
    BMedia,
    BAvatar,
    AppTimeline,
    AppTimelineItem,
    BFormRating,
    BButton,

    formatDateTimeOnlyNumber,
  },
  directives: {
    'b-tooltip': VBTooltip,
  },
  props: {
    servicesData: {
      type: Array,
      required: true,
    },
    hiddenButtonService: {
      type: Boolean,
      required: true,
    },
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
    
    return {
      formatDateTimeOnlyNumber
    }
  },
}
</script>

<style>

</style>
