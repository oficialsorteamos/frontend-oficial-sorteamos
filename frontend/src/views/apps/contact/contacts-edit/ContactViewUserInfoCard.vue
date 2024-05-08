<template>
  <b-card>

    <b-row>

      <!-- User Info: Left col -->
      <b-col
        cols="12"
        xl="12"
        class="d-flex justify-content-between flex-column"
      >
        <div class="d-flex justify-content-between">
          <h6 class="section-label">
          </h6>
          <!-- Botão para editar os dados do contato -->
          <feather-icon icon="EditIcon" 
            size="17"
            class="cursor-pointer d-sm-block d-none"
            v-b-modal.modal-edit-contact
            v-b-tooltip.hover.v-secondary
            title="Edit"
          />
        </div>
        <!-- User Avatar & Action Buttons -->
        <div class="d-flex justify-content-start h-100">
          <span
            @click="$refs.importFile.$el.click()"
            style="cursor: pointer"
          >
          <b-avatar
            :src="'../../../'+userData.con_avatar"
            :text="avatarText(userData.fullName)"
            :variant="`light-${resolveUserRoleVariant(userData.role)}`"
            size="104px"
            rounded
          />
          </span>
          <b-form-file
            ref="importFile"
            name="importFile"
            id="importFile"
            accept=".jpeg, .jpg, .png"
            :hidden="true"
            plain
            v-on:change="uploadPhoto"
          />
          <div class="d-flex ml-1 mt-1">
            <div class="mb-1">
              <h4 class="mb-0">
                {{ userData.con_name }}
              </h4>
              <span
                v-if="userData.tags.length > 0"
              >
                <span
                  v-for="tag in userData.tags"
                  :key="tag.id"
                >
                  <b-badge 
                    :style="'margin-right: 4px; margin-bottom: 3px; background-color:'+tag.tag_color"
                  >
                    <span class="card-text">{{ tag.tag_name }}</span>
                  </b-badge>
                </span>
              </span>
              <span
                v-else
              >
                <b-badge 
                  variant="light-secondary"
                  
                >
                  <span class="card-text">{{ $t('contacts.contactViewUserInfoCard.none') }}</span>
                </b-badge>
              </span>
              <div
                style="margin-left: -20px !important" 
              >
                <b-form-rating
                  id="rating-lg-no-border"
                  v-model="userData.avg_rating_service"
                  no-border
                  variant="warning"
                  size="lg"
                  inline
                  :readonly="true"
                  v-b-tooltip.hover.v-secondary
                  :title="userData.avg_rating_service"
                />
              </div>
            </div>
          </div>
        </div>

        <!-- User Stats -->
        <!--
        <div class="d-flex align-items-center mt-2">
          <div class="d-flex align-items-center mr-2">
            <b-avatar
              variant="light-primary"
              rounded
            >
              <feather-icon
                icon="DollarSignIcon"
                size="18"
              />
            </b-avatar>
            <div class="ml-1">
              <h5 class="mb-0">
                23.3k
              </h5>
              <small>Monthly Sales</small>
            </div>
          </div>

          <div class="d-flex align-items-center">
            <b-avatar
              variant="light-success"
              rounded
            >
              <feather-icon
                icon="TrendingUpIcon"
                size="18"
              />
            </b-avatar>
            <div class="ml-1">
              <h5 class="mb-0">
                $99.87k
              </h5>
              <small>Annual Profit</small>
            </div>
          </div>
        </div>
        -->
      </b-col>

      <!-- Right Col: Table -->
      <b-col
        cols="12"
        xl="12"
        class="mt-2"
      >
        <table class="mt-2 mt-xl-0 w-100">
          <tr>
            <th class="pb-50">
              <feather-icon
                icon="PhoneIcon"
                class="mr-75"
              />
              <span class="font-weight-bold">{{ $t('contacts.contactViewUserInfoCard.phone') }}</span>
            </th>
            <td>
              {{ userData.con_phone | VMask(' +## (##) #####-####') }}
            </td>
          </tr>
          <tr>
            <th class="pb-50">
              <feather-icon
                icon="MailIcon"
                class="mr-75"
              />
              <span class="font-weight-bold">{{ $t('contacts.contactViewUserInfoCard.email') }}</span>
            </th>
            <td class="pb-50">
              <span v-if="userData.email"> {{ userData.email }} </span>
              <span v-else> - </span>
            </td>
          </tr>
          <tr>
            <th class="pb-50">
              <feather-icon
                icon="MapPinIcon"
                class="mr-75"
              />
              <span class="font-weight-bold">{{ $t('contacts.contactViewUserInfoCard.state') }}</span>
            </th>
            <td 
              class="pb-50 text-capitalize"
              v-if="userData.state"
            >
              {{ userData.state.sta_name }}
            </td>
            <td 
              class="pb-50 text-capitalize"
              v-else
            >
              -
            </td>
          </tr>
          <tr>
            <th class="pb-50">
              <feather-icon
                icon="UsersIcon"
                class="mr-75"
              />
              <span class="font-weight-bold">{{ $t('contacts.contactViewUserInfoCard.gender') }}</span>
            </th>
            <td class="pb-50 text-capitalize">
              {{ userData.gender.gen_description }}
            </td>
          </tr>
          <tr>
            <th class="pb-50">
              <feather-icon
                icon="CalendarIcon"
                class="mr-75"
              />
              <span class="font-weight-bold">{{ $t('contacts.contactViewUserInfoCard.birthday') }}</span>
            </th>
            <td class="pb-50 text-capitalize">
              <span v-if="userData.birthday"> {{ formatDate(userData.birthday) }} </span>
              <span v-else> - </span>
            </td>
          </tr>
          <tr>
            <th class="pb-50">
              <feather-icon
                icon="UserPlusIcon"
                class="mr-75"
              />
              <span class="font-weight-bold">{{ $t('contacts.contactViewUserInfoCard.created') }}</span>
            </th>
            <td class="pb-50">
              {{ formatDateTime(userData.created_at) }}
            </td>
          </tr>
        </table>
      </b-col>
    </b-row>
    <!-- Form para editar dados do contato -->
    <b-modal
      id="modal-edit-contact"
      :title="$t('contacts.contactViewUserInfoCard.editContact')"
      hide-footer
      size="sm"
    >
      <contact-modal-edit-contact-handler
        :contact="userData"
        :clear-contact-data="clearContactData"
        @update-contact="updateContact"
        @hide-modal="hideModal"
      />
    </b-modal>
  </b-card>
</template>

<script>
import {
  BCard, BButton, BAvatar, BRow, BCol, BBadge, BFormRating, VBTooltip, VBModal, BFormFile,
} from 'bootstrap-vue'
import {
  ref
} from '@vue/composition-api'
import useUsersList from '../contacts-list/useUsersList'
import { VueMaskFilter } from 'v-mask'
import store from '@/store'
import { avatarText, formatDate, formatDateTime } from '@core/utils/filter'
import ContactModalEditContactHandler from './contact-modal-edit-contact-handler/ContactModalEditContactHandler.vue'

// Notification
import { useToast } from 'vue-toastification/composition'
import ToastificationContent from '@core/components/toastification/ToastificationContent.vue'
import Vue from 'vue'
Vue.filter('VMask', VueMaskFilter)

export default {
  components: {
    BCard, 
    BButton, 
    BRow, 
    BCol, 
    BAvatar, 
    BBadge,
    BFormRating,
    VBModal,
    BFormFile,

    //Formata a data
    formatDate,
    formatDateTime,

    ContactModalEditContactHandler,
  },
  props: {
    userData: {
      type: Object,
      required: true,
    },
  },
  directives: {
    'b-tooltip': VBTooltip,
  },
  methods: {
    hideModal(modalName) {
      //Fecha o Modal
      this.$root.$emit('bv::hide::modal', modalName, '#btnShow')
    },
    openModal(modalName) {
      console.log(modalName)
      //Abre o Modal
      this.$root.$emit('bv::show::modal', modalName, '#btnShow')
    },
  },
  setup(props, { emit }) {
    const { resolveUserRoleVariant } = useUsersList()

    //Toast Notification
    const toast = useToast()

    const blankContact = {
    }
    const contactData = ref(JSON.parse(JSON.stringify(blankContact)))
    //Limpa os dados do popup
    const clearContactData = () => {
      contactData.value = JSON.parse(JSON.stringify(blankContact))
    }

    //Atualiza os dados do contato
    const updateContact = contactData => {
      store.dispatch('app-contact/updateContact', { contactData: contactData })
        .then(() => {
          emit('get-contact')  

          toast({
            component: ToastificationContent,
            props: {
              title: 'Contato atualizado com sucesso!',
              icon: 'CheckIcon',
              variant: 'success',
            },
          })
        })
    }

    const photo = ref('')
    
    //Faz o upload da foto de avatar do contato
    const uploadPhoto = (event) => {
      photo.value = event.target.files[0]
      
      const formData = new FormData()
      formData.append('name', 'ivahy.jpg')
      formData.append('file', photo.value)
      formData.append('chatId', props.userData.chat.id)
      formData.append('contactId', props.userData.id)
      
      const config = {
          headers: {
            'content-type': 'multipart/form-data'
          }
      }
      store.dispatch('app-contact/uploadPhoto', formData, config)
        .then(() => {
          emit('get-contact')

          toast({
            component: ToastificationContent,
            props: {
              title: 'Avatar atualizado com sucesso!',
              icon: 'CheckIcon',
              variant: 'success',
            },
          })
      })
    }

    return {
      avatarText,
      resolveUserRoleVariant,

      formatDate,
      formatDateTime,

      ContactModalEditContactHandler,
      updateContact,
      clearContactData,

      uploadPhoto,
    }
  },
}
</script>

<style>

</style>
