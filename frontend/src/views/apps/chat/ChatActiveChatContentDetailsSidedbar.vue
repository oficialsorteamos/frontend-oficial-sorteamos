<template>
  <div
    class="user-profile-sidebar"
    :class="{'show': shallShowActiveChatContactSidebar}"
  >
    <!-- Header -->
    <header
      v-if="contact"
      class="user-profile-header"
    >
      <span class="close-icon">
        <feather-icon
          icon="XIcon"
          @click="$emit('update:shall-show-active-chat-contact-sidebar', false)"
        />
      </span>

      <div class="header-profile-sidebar">
        <div class="avatar box-shadow-1 avatar-xl avatar-border">
          <b-avatar
            size="70"
            :src="contact.con_avatar? baseUrlStorage+contact.con_avatar : null"
            :variant="contact.con_avatar != null ? 'transparent' : 'light-'+contact.avatarColor"
            :text="contact.initialsName != null ? contact.initialsName : 'CL'"
          />
          <span
            class="avatar-status-xl"
            :class="`avatar-status-${contact.status}`"
          />
        </div>
        <h4 class="chat-user-name">
          {{ contact.con_name }}
        </h4>
        <span 
          v-if="contact.tag" 
          class="user-post text-capitalize"
        >
          <b-badge variant="light-secondary">
            {{ contact.tag.tag_name }}
          </b-badge>
        </span>
      </div>
    </header>

    <!-- User Details -->
    <vue-perfect-scrollbar
      :settings="perfectScrollbarSettings"
      class="user-profile-sidebar-area scroll-area"
    >

      <!-- About -->
      <!--
      <h6 class="section-label mb-1">
        {{ $t('chat.chatActiveChatContentDetailsSidedbar.about') }}
      </h6>
      <p>{{ contact.about }}</p>
      -->
      <!-- Personal Info -->
      <div class="personal-info">
        <div class="d-flex justify-content-between">
          <h6 class="section-label ">
            {{ $t('chat.chatActiveChatContentDetailsSidedbar.personalInformation') }}
          </h6>
          <!-- Se não for um bloco final -->
          <feather-icon icon="EditIcon" 
            size="17"
            class="cursor-pointer d-sm-block d-none mr-1"
            v-b-modal.modal-contact-edit
          />
        </div>
        <ul class="list-unstyled">
          <li class="mb-1">
            <feather-icon
              icon="UserIcon"
              size="16"
              class="mr-75"
            />
            <span class="align-middle" v-if="contact.gender">{{ contact.gender.gen_description }}</span>
            <span class="align-middle" v-else>-</span>
          </li>
          <li class="mb-1">
            <feather-icon
              icon="PhoneCallIcon"
              size="16"
              class="mr-75"
            />
            <span class="align-middle" v-if="contact.con_phone">{{ contact.con_phone | VMask(' +## (##) #####-####') }}</span>
            <span class="align-middle" v-else>-</span>
          </li>
          <li class="mb-1">
            <feather-icon
              icon="UserCheckIcon"
              size="16"
              class="mr-75"
            />
            <span class="align-middle" v-if="contact.con_cpf">{{ contact.con_cpf | VMask('###.###.###-##') }}</span>
            <span class="align-middle" v-else>-</span>
          </li>
          <li class="mb-1">
            <feather-icon
              icon="BriefcaseIcon"
              size="16"
              class="mr-75"
            />
            <span class="align-middle" v-if="contact.con_cnpj">{{ contact.con_cnpj | VMask('##.###.###/####-##') }}</span>
            <span class="align-middle" v-else>-</span>
          </li>
          <li class="mb-1">
            <feather-icon
              icon="MailIcon"
              size="16"
              class="mr-75"
            />
            <span class="align-middle" v-if="contact.con_email">{{ contact.con_email }}</span>
            <span class="align-middle" v-else>-</span>
          </li>
          <li>
            <feather-icon
              icon="CalendarIcon"
              size="16"
              class="mr-75"
            />
            <span class="align-middle" v-if="contact.con_birthday">{{ formatDateBirthDay(contact.con_birthday) }}</span>
            <span class="align-middle" v-else>-</span>
          </li>
          <li
            class="mt-1"
          >
            <!-- Se o usuário tiver algum endereço cadastrado -->
            <span
              v-if="contact.addresses"
            >
              <span
                v-for="address in contact.addresses"
                :key="address.id"
              >
                <feather-icon
                  icon="MapPinIcon"
                  size="16"
                  class="mr-75"
                />
                <span class="align-middle" >{{ address.street }}, {{ address.number? 'Nº'+address.number: 'S/N' }}, {{ address.district }}, {{ address.city }}, {{ address.state.sta_name }}, {{address.country.cou_name}}, CEP: {{ address.cep }}</span>
              </span>
            </span>
            <span
              v-else
            >
              -
            </span>
          </li>
        </ul>
      </div>

      <!-- More Options -->
      <div class="more-options">
        <h6 class="section-label mb-1 mt-3">
          {{ $t('chat.chatActiveChatContentDetailsSidedbar.options') }}
        </h6>

        <!-- types -->
        <app-collapse 
          accordion
        >
          <b-card
            no-body
            :class="{'open': true}"
            @mouseenter="collapseOpen"
            @mouseleave="collapseClose"
          >
            <b-card-header
              :class="{'collapsed': !visibleOservation}"
              :aria-expanded="visibleOservation ? 'true' : 'false'"
              :aria-controls="collapseItemID"
              role="tab"
              data-toggle="collapse"
              @click="updateVisible(!visibleOservation, 'a')"
            >
              <slot name="header">
                <span class="lead collapse-title">{{ $t('chat.chatActiveChatContentDetailsSidedbar.observations') }}</span>
              </slot>
            </b-card-header>
            
            

            <b-collapse
              :id="collapseItemID"
              v-model="visibleOservation"
              :accordion="accordion"
              role="tabpanel"
            >
              <b-card-body>
                <div
                  class="scroll-chat-observations"
                >
                  <b-row
                    class="mb-2"
                  >
                    <b-col
                      cols="10"
                    >
                      <b-form-textarea
                        v-model="observationMessage"
                        :placeholder="$t('chat.chatActiveChatContentDetailsSidedbar.writeObservation')"
                        ref="inputObservationMessage"
                        style="height: 38px"
                        id="input-observation-message"
                        no-resize
                      />
                    </b-col>
                    <b-col
                      cols="2"
                    >
                      <b-button
                        variant="primary"
                        type="submit"
                        class="btn-icon"
                        size="md"
                        ref="submitChatObservation"
                        :disabled="observationMessage.length > 0? false : true"
                        @click="$emit('add-chat-observation', observationMessage); observationMessage = ''"
                      >
                        <feather-icon 
                          icon="PlusIcon"
                        />
                      </b-button>
                    </b-col>
                  </b-row>

                  <div
                    v-if="chatObservations.length > 0"
                  >
                    <app-timeline
                      class="p-2"
                    >
                      
                      <app-timeline-item
                        v-for="observation in chatObservations"
                        :key="observation.id"
                        variant="primary"
                        icon="MessageCircleIcon"
                      >
                        <span
                          v-if="observation.id"
                        >
                          <b-row>
                            <b-col
                              cols="11"
                            >
                              <p class="text-justify;" style="white-space: break-spaces;">{{ observation.cha_observation }}</p>
                            </b-col>
                            <b-col
                              cols="1"
                            >
                              <feather-icon 
                                icon="TrashIcon" 
                                size="16"
                                class="cursor-pointer"
                                @click="$emit('remove-chat-observation', observation.id)"
                              />
                            </b-col>
                          </b-row>
                          <b-media>
                            <template #aside>
                              <b-avatar :src="baseUrlStorage+observation.avatar" />
                            </template>
                            <h6>{{observation.name}} ({{observation.dep_name}})</h6>
                            <small class="text-muted">{{ formatDateTime(observation.updated_at) }}</small>
                          </b-media>
                        </span>
                      </app-timeline-item>
                      
                    </app-timeline>
                    <div class="text-center">
                      <b-button
                        variant="outline-primary"
                        class="btn-icon rounded-circle mb-1"
                        @click="$emit('fetch-chat-observations', {chatId: chat.id, offset: offset}); offset++"
                        :hidden="hiddenButtonChatObservations"
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
                    <h6>{{ $t('chat.chatActiveChatContentDetailsSidedbar.noObservation') }}</h6>
                  </div>
                </div>
              </b-card-body>
            </b-collapse>
          </b-card>
        </app-collapse>
        
        <!-- Mídias -->
        <app-collapse 
          accordion
        >
          <b-card
            no-body
            :class="{'open': true}"
            @mouseenter="collapseOpen"
            @mouseleave="collapseClose"
          >
            <b-card-header

              :class="{'collapsed': !visible}"
              :aria-expanded="visible ? 'true' : 'false'"
              :aria-controls="collapseItemID"
              role="tab"
              data-toggle="collapse"
              @click="updateVisible(!visible, 'b')"
            >
              <slot name="header">
                <span class="lead collapse-title">{{ $t('chat.chatActiveChatContentDetailsSidedbar.medias') }}</span>
              </slot>
            </b-card-header>

            <b-collapse
              :id="collapseItemID"
              v-model="visible"
              :accordion="accordion"
              role="tabpanel"
            >
              <b-card-body>
                <b-tabs
              pills
              align="center"
            >
              <b-tab
                :title="$t('chat.chatActiveChatContentDetailsSidedbar.images')"
                size="sm"
                active
              >
                <span
                  v-if="chat.img != ''"
                >
                  <swiper
                    class="swiper"
                    :options="swiperOptions"
                    :dir="$store.state.appConfig.isRTL ? 'rtl' : 'ltr'"
                  >
                    <swiper-slide
                      v-for="(data,index) in chat.img"
                      :key="index"
                    >
                      <viewer 
                        :options="{inline: false, button: false, navbar: false, title: false, toolbar: true, tooltip: true, movable: false, zoomable: true, rotatable: true, scalable: true, transition: true, fullscreen: true, keyboard: true}"
                      >
                        <b-img
                          :src="`../storage/chats/chat${data.chat_id}/images/${data.mes_content_name}`"
                          fluid
                          style="max-height: 150px"
                        />
                      </viewer>
                    </swiper-slide>

                    <!-- Add Arrows 
                    <div
                      slot="button-next"
                      class="swiper-button-next"
                    />
                    <div
                      slot="button-prev"
                      class="swiper-button-prev"
                    />
                    -->
                    <div
                      slot="pagination"
                      class="swiper-pagination"
                    />
                  </swiper>
                </span>
                <span
                  v-else
                >
                  <h6
                    class="text-center p-1"
                  >
                    {{ $t('chat.chatActiveChatContentDetailsSidedbar.noImagesFound') }}
                  </h6>
                </span>
              </b-tab>

              <b-tab 
                :title="$t('chat.chatActiveChatContentDetailsSidedbar.files')"
                size="sm"
              >
                <span
                  v-if="chat.files != ''"
                >
                  <swiper
                    class="swiper"
                    :options="swiperOptions"
                    :dir="$store.state.appConfig.isRTL ? 'rtl' : 'ltr'"
                  >
                    <swiper-slide
                      v-for="(data,index) in chat.files"
                      :key="index"
                      class="swiper-file"
                    >
                      <a
                        target="_blank" 
                        :href="`../storage/chats/chat${data.chat_id}/files/${data.mes_content_name}`"
                      > 
                        <font-awesome-icon 
                          icon="file-pdf" 
                          size="6x" 
                          v-if="data.mes_content_type == 'application/pdf'"
                          style="color: #ea5455 !important;"
                        />
                        <font-awesome-icon 
                        icon="file" 
                        size="6x" 
                        v-else
                        />
                        <p>{{data.mes_media_original_name}}</p>
                      </a> 
                    </swiper-slide>

                    <!-- Add Arrows 
                    <div
                      slot="button-next"
                      class="swiper-button-next"
                    />
                    <div
                      slot="button-prev"
                      class="swiper-button-prev"
                    />
                    -->
                    <div
                      slot="pagination"
                      class="swiper-pagination"
                    />
                  </swiper>
                </span>
                <span
                  v-else
                >
                  <h6
                    class="text-center p-1"
                  >
                    {{ $t('chat.chatActiveChatContentDetailsSidedbar.noFilesFound') }}
                  </h6>
                </span>
              </b-tab>
            </b-tabs>
              </b-card-body>
            </b-collapse>
          </b-card>
        </app-collapse>
      </div>
    </vue-perfect-scrollbar>
    <!-- Modal com o form para cadastro de evento na agenda -->
    <b-modal
      id="modal-contact-edit"
      title="Edit Contact"
      hide-footer
      size="lg"
    >
      <chat-modal-edit-contact-handler
        :contact="contact"
        @set-contact="setContact"
        @set-address-contact="setAddressContact"
      />
    </b-modal>
  </div>
</template>

<script>

import AppCollapse from '@core/components/app-collapse/AppCollapse.vue'
import AppCollapseItem from '@core/components/app-collapse/AppCollapseItem.vue'
import VuePerfectScrollbar from 'vue-perfect-scrollbar'
import { BAvatar, BBadge, BTabs, BTab, BCardText, BImg, BCollapse, BCard, BCardBody, BCardHeader, BFormTextarea, BButton,
        VBTooltip, BMedia, BRow, BCol, } from 'bootstrap-vue'
import { VueMaskFilter } from 'v-mask'
import { formatDate, formatDateTime } from '@core/utils/filter'
import AppTimeline from '@core/components/app-timeline/AppTimeline.vue'
import AppTimelineItem from '@core/components/app-timeline/AppTimelineItem.vue'
import ChatModalEditContactHandler from '@/views/apps/chat/chat-modal-edit-contact-handler/ChatModalEditContactHandler.vue'
import { Swiper, SwiperSlide } from 'vue-awesome-swiper'
import VueViewer from 'v-viewer'
import { library } from '@fortawesome/fontawesome-svg-core'
import { faFilePdf, faFile } from '@fortawesome/free-solid-svg-icons'
import { v4 as uuidv4 } from 'uuid'

library.add(faFilePdf, faFile)

import 'swiper/css/swiper.css'

import Vue from 'vue'
Vue.filter('VMask', VueMaskFilter)
Vue.use(VueViewer)


export default {
  components: {
    // BSV
    BAvatar,
    BBadge,
    BTabs,
    BTab,
    BCardText,
    BImg,
    BCollapse,
    BCard,
    BCardBody,
    BCardHeader,
    BFormTextarea,
    BButton,
    BMedia,
    BRow,
    BCol,

    //Swiper
    Swiper,
    SwiperSlide,

    // 3rd Party
    VuePerfectScrollbar,

    //Máscara
    VueMaskFilter,

    //Formata a data
    formatDate,
    formatDateTime,

    //Collapse
    AppCollapse,
    AppCollapseItem,


    VueViewer,
    ChatModalEditContactHandler,

    AppTimeline,
    AppTimelineItem,
  },
  directives: {
    'b-tooltip': VBTooltip,
  },
  data: () => {
    return {
      swiperOptions: {
        observer: true,
        slidesPerColumn: 2,
        slidesPerView: 3,
        spaceBetween: 5,
        pagination: {
          el: '.swiper-pagination',
          clickable: true,
        },
      },
      visibleOservation: false,
      visible: true,
      observationMessage: '',
      offset: 1,
    }
  },
  props: {
    shallShowActiveChatContactSidebar: {
      type: Boolean,
      required: true,
    },
    contact: {
      type: Object,
      required: true,
    },
    chat: {
      type: Object,
      //required: true,
    },
    baseUrlStorage: {
      type: String,
      required: true,
    },
    chatObservations: {
      type: Array,
      required: true,
    },
    hiddenButtonChatObservations: {
      type: Boolean,
      required: true,
    },
  },
  computed: {
    accordion() {
      return this.$parent.accordion ? `accordion-${this.$parent.collapseID}` : null
    },
  },
  created() {
    this.collapseItemID = uuidv4()
    this.visible = this.isVisible
  },
  methods: {
    updateVisible(val = true, accordion) {
      if(accordion == 'a') {
        this.visibleOservation = val
      }
      else if(accordion == 'b') {
        this.visible = val
      }
      this.$emit('visible', val)
    },
    collapseOpen() {
      if (this.openOnHover) this.updateVisible(true)
    },
    collapseClose() {
      if (this.openOnHover) this.updateVisible(false)
    },
    formatDateBirthDay(birthday) {
      //Se houver data de aniversário cadastrada
      if(birthday) {
        //Formata a data
        var newDate = birthday.replace(/-/g, '/')
        return newDate
      }
    }
  },
  setup(props, { emit }) {
    const perfectScrollbarSettings = {
      maxScrollbarLength: 150,
    }

    const setContact = contactData => {
      emit('set-contact', contactData)
    }

    const setAddressContact = contactData => {
      emit('set-address-contact', contactData)
    }

    return {
      perfectScrollbarSettings,
      formatDate,
      formatDateTime,
      AppCollapse,
      AppCollapseItem,
      BTabs,
      BTab,
      BCardText,
      Swiper,
      SwiperSlide,
      BImg,
      VueViewer,

      setContact,
      setAddressContact,
    }
  },
}
</script>

<style lang="scss" scoped>
  .scroll-chat-observations {
    max-height: 300px; 
    min-height: 180px; 
    overflow-y: scroll;
    overflow-x: hidden;
  }

  .swiper {
    height: 410px;
    margin-left: auto;
    margin-right: auto;

    .swiper-slide {
      height: 200px;
    }
  }
  
  .swiper-slide {
    //display: flex;
    //justify-content: center;
    //align-items: center;
    width: 200px;
    height: 200px;
    //text-align: center;
    //font-weight: bold;
    //font-size: $font-size-huge * 2;
    //background-color: #2C8DFB;
    //background-position: center;
    //background-size: cover;
    //color: white;
  }

  .swiper-file {
    width: 90px;
  }

</style>
