<template>
  <b-card
    no-body
    class="card-developer-meetup"
  >
    <!--
    <div class="bg-light-primary rounded-top text-center">
      <b-img
        :src="require('@/assets/images/illustration/email.svg')"
        alt="Meeting Pic"
        height="170"
      />
    </div>
    -->
    <b-card-body>
      <!-- metting header -->
      <div class="meetup-header d-flex align-items-center">
        <div class="meetup-day">
          <font-awesome-icon 
            :icon="['fab', 'whatsapp']" 
            size="3x" 
            style="color: #34af23 !important;"
          />
        </div>
        <div class="my-auto">
          <b-card-title class="mb-25">
            {{ costData.contact_mailing.campaign.cam_name }}
          </b-card-title>
          <b-card-text class="mb-0">
            {{ costData.contact_mailing.campaign.cam_description }}
          </b-card-text>
        </div>
      </div>
      <!--/ metting header -->

      <b-row>
        <b-col
          cols="12"
          sm="3"
        >
          <b-media
            no-body
          >
            <b-media-aside class="mr-1">
              <b-avatar
                rounded
                variant="light-primary"
                size="34"
              >
                <feather-icon
                  icon="UserIcon"
                  size="18"
                />
              </b-avatar>
            </b-media-aside>
            <b-media-body>
              <h6 class="mb-0">
                {{ $t('cost.costDetails.contact') }}
              </h6>
              <small>{{ costData.contact_mailing.contact.con_name }} ({{ costData.contact_mailing.contact.con_phone | VMask('+## (##) #####-####') }})</small>
            </b-media-body>
          </b-media>
        </b-col>
        <b-col
          cols="12"
          sm="1"
        >
          <small class="text-muted mr-75">
            <feather-icon
              icon="RepeatIcon"
              size="20"
            />
          </small>
        </b-col>
        <b-col
          cols="12"
          sm="3"
          v-if="costData.contact_mailing.channel"
        >
          <b-media
            no-body
          >
            <b-media-aside class="mr-1">
              <b-avatar
                rounded
                variant="light-success"
                size="34"
              >
                <feather-icon
                  icon="PhoneIcon"
                  size="18"
                />
              </b-avatar>
            </b-media-aside>
            <b-media-body
            >
              <h6 class="mb-0">
                {{ $t('cost.costDetails.channel') }}
                <span
                  v-if="costData.contact_mailing.channel.cha_api_official"
                >
                  <feather-icon
                    icon="CheckCircleIcon"
                    size="11"
                    stroke="#089dea"
                    style="margin-bottom: 2px"
                    v-b-tooltip.hover.v-secondary
                    :title="$t('cost.costDetails.officialChannel')"
                  />
                </span>
              </h6>
              <small>{{ costData.contact_mailing.channel.cha_name }} ({{ costData.contact_mailing.channel.cha_phone_number | VMask('(##) #####-####') }})</small>
            </b-media-body>
          </b-media>
        </b-col>
      </b-row>
    </b-card-body>
  </b-card>
</template>

<script>
import {
  BCard, BImg, BCardBody, BCardText, BCardTitle, BMedia, BMediaAside, BAvatar, BAvatarGroup, VBTooltip, BMediaBody,
  BRow, BCol,
} from 'bootstrap-vue'
import { VueMaskFilter } from 'v-mask'
import { faWhatsapp } from '@fortawesome/free-brands-svg-icons'
import { library } from '@fortawesome/fontawesome-svg-core'
import Vue from 'vue'
Vue.filter('VMask', VueMaskFilter)
library.add( faWhatsapp)
/* eslint-disable global-require */
export default {
  components: {
    BCard,
    BImg,
    BCardBody,
    BCardText,
    BCardTitle,
    BMedia,
    BMediaAside,
    BAvatar,
    BAvatarGroup,
    BMediaBody,
    BRow,
    BCol,
  },
  directives: {
    'b-tooltip': VBTooltip,
  },
  data() {
    return {
      mediaData: [
        { avatar: 'CalendarIcon', title: 'Sat, May 25, 2020', subtitle: '10:AM to 6:PM' },
        { avatar: 'MapPinIcon', title: 'Central Park', subtitle: 'Manhattan, New york City' },
      ],
      avatars: [
        { avatar: require('@/assets/images/portrait/small/avatar-s-9.jpg'), fullName: 'Billy Hopkins' },
        { avatar: require('@/assets/images/portrait/small/avatar-s-6.jpg'), fullName: 'Amy Carson' },
        { avatar: require('@/assets/images/portrait/small/avatar-s-8.jpg'), fullName: 'Brandon Miles' },
        { avatar: require('@/assets/images/portrait/small/avatar-s-7.jpg'), fullName: 'Daisy Weber' },
        { avatar: require('@/assets/images/portrait/small/avatar-s-20.jpg'), fullName: 'Jenny Looper' },
      ],
    }
  },
  props: {
    costData: {
      type: Object,
      required: true,
    },
  },
}
</script>
