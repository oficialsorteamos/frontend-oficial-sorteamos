<template>
  <div>
    <!-- A função handleSubmit só deixa a o formulário ser submetido (só chama a função onSubmit) caso todos os campos do form satisfação os os pré-requisitos -->
    <validation-observer
      #default="{ handleSubmit }"
      ref="refFormObserver"
    >
      <b-form
        enctype="multipart/form-data"
        @submit.prevent="handleSubmit(onSubmit)"
      >

        <!-- Channels -->
        <validation-provider
          #default="{ errors }"
          :name="$t('services.serviceModalAddServiceHandler.channels')"
          rules="required"
        >
          <b-form-group
            :label="$t('services.serviceModalAddServiceHandler.channels') + '*'"
            label-for="vue-select"
            :state="errors.length > 0 ? false:null"
          >
            <v-select
              id="vue-select"
              v-model="serviceLocal.channel"
              :dir="$store.state.appConfig.isRTL ? 'rtl' : 'ltr'"
              :options="channels"
              :getOptionLabel="channels => channels.cha_name"
              transition=""
            >
              <template #search="{attributes, events}">
                <input
                  class="vs__search"
                  :required="!serviceLocal.channel"
                  v-bind="attributes"
                  v-on="events"
                />
              </template>
            </v-select>
            
            <b-form-invalid-feedback :state="errors.length > 0 ? false:null">
              {{ errors[0] }}
            </b-form-invalid-feedback>
          </b-form-group>
        </validation-provider>


        <!-- Guests -->
        <validation-provider
          #default="{ errors }"
          :name="$t('services.serviceModalAddServiceHandler.contacts')"
          rules="required"
        >
          <b-form-group
            :label="$t('services.serviceModalAddServiceHandler.contacts') + '*'"
            label-for="add-guests"
            :state="errors.length > 0 ? false:null"
          >
            <!-- <b-row class="breadcrumbs-top"> -->
            <b-row>
              <b-col cols="10">
                <v-select
                  v-model="serviceLocal.contacts"
                  :dir="$store.state.appConfig.isRTL ? 'rtl' : 'ltr'"
                  multiple
                  :close-on-select="false"
                  :options="contactsSearch"
                  :getOptionLabel="contactsSearch => contactsSearch.con_name"
                  label="name"
                  input-id="add-guests"
                  @search="fuseSearch"
                  @input="checkGuestExist"
                  :filterable="false"
                >
                  <template #option="{ con_avatar, con_name }">
                    <b-avatar
                      size="sm"
                      :src="'../../../'+con_avatar"
                    />
                    <span class="ml-50 align-middle"> {{ con_name }}</span>
                  </template>
  
                  <template #selected-option="{ con_avatar, con_name }">
                    <b-avatar
                      size="sm"
                      class="border border-white"
                      :src="'../../../'+con_avatar"
                    />
                    <span class="ml-50 align-middle"> {{ con_name }}</span>
                  </template>
                </v-select>
              </b-col>

              <b-col cols="2">
                <b-button
                  variant="outline-primary"
                  class="btn-icon"
                  v-b-tooltip.hover.v-secondary
                  v-b-modal.modal-edit-contact
                  :title="$t('services.serviceModalAddServiceHandler.addContact')"
                >
                  <feather-icon 
                    icon="PlusIcon" 
                    size="16"
                  />
                </b-button>
              </b-col>
            </b-row>

            <b-form-invalid-feedback :state="errors.length > 0 ? false:null">
              {{ errors[0] }}
            </b-form-invalid-feedback>

            <span 
              v-if="errorMessage"
              style="font-size: 0.857rem; color: #ea5455;"
            >
              {{ $t('services.serviceModalAddServiceHandler.contactsAlreadyInService') }}
            </span>
          </b-form-group>
          
        </validation-provider>

        <!-- Departments -->
        <validation-provider
          #default="{ errors }"
          :name="$t('services.departments')"
          rules="required"
        >
          <b-form-group
            :label="$t('services.departments') + '*'"
            label-for="vue-select"
            :state="errors.length > 0 ? false:null"
          >
            <v-select
              id="vue-select"
              v-model="localDepartmentSelected"
              :dir="$store.state.appConfig.isRTL ? 'rtl' : 'ltr'"
              :options="departments"
              :getOptionLabel="departments => departments.dep_name"
              transition=""
            />

            <b-form-invalid-feedback :state="errors.length > 0 ? false:null">
              {{ errors[0] }}
            </b-form-invalid-feedback>
          </b-form-group>
        </validation-provider>

        <!-- Users -->
        <b-form-group
          :label="$t('services.users')"
          label-for="vue-select"
          v-show="localDepartmentSelected"
        >
          <v-select
            id="vue-select"
            v-model="serviceLocal.user"
            :dir="$store.state.appConfig.isRTL ? 'rtl' : 'ltr'"
            :options="departmentUsers"
            :getOptionLabel="departmentUsers => departmentUsers.name"
            transition=""
          />
        </b-form-group>

        <!-- Form Actions -->
        <div class="d-flex mt-2 modal-footer">
          <b-button
            v-ripple.400="'rgba(255, 255, 255, 0.15)'"
            variant="primary"
            class="mr-2"
            :disabled="errorMessage"
            type="submit"
          >
            {{ $t('services.add') }}
          </b-button>
        </div>
      </b-form>
    </validation-observer>

    <!-- Form para editar dados do contato -->
    <b-modal
      id="modal-edit-contact"
      :title="$t('contacts.contactViewUserInfoCard.addContact')"
      hide-footer
      size="sm"
    >
      <contact-modal-edit-contact-handler
        :contact="contactData"
        :clear-contact-data="clearContactData"
        @add-contact="addContact"
        @hide-modal="hideModal"
      />
    </b-modal>
      
  </div>
</template>

<script>
import {
  BAvatar, BButton, BModal, VBModal, BForm, BFormInput, BFormGroup, BFormInvalidFeedback, BFormDatepicker, VBTooltip, BRow, BCol
} from 'bootstrap-vue'
import vSelect from 'vue-select'
import Ripple from 'vue-ripple-directive'
import axios from '@axios'
import { ref, watch, toRefs } from '@vue/composition-api'
import { ValidationProvider, ValidationObserver } from 'vee-validate'
import useServiceModalAddServiceHandler from './useServiceModalAddServiceHandler'
import ContactModalEditContactHandler from '../../contact/contacts-edit/contact-modal-edit-contact-handler/ContactModalEditContactHandler.vue'
import formValidation from '@core/comp-functions/forms/form-validation'
import VuePhoneNumberInput from 'vue-phone-number-input'
import useContactList from './useContactList'
import useDepartmentUsersList from './useDepartmentUsersList'

export default {
  components: {
    BAvatar,
    BButton,
    BModal,
    BForm,
    BFormInput,
    BFormGroup,
    vSelect,
    BFormInvalidFeedback,
    BFormDatepicker,
    BRow,
    BCol,

    // Form Validation
    ValidationProvider,
    ValidationObserver,

    VuePhoneNumberInput,

    ContactModalEditContactHandler,
  },
  directives: {
    'b-modal': VBModal,
    'b-tooltip': VBTooltip,
    Ripple,
  },
  props: {
    service: {
      type: Object,
      required: true,
    },
    clearServiceData: {
      type: Function,
      required: true,
    }
  },
  data() {
    return {
      channels: [],
      guestsOptions: [],
      departments: []
    }
  },
  methods: {
    //Insere emojis
    // setPhoneNumber: function(data) {
    //   this.contactLocal.phoneNumber = data.formattedNumber
    // },
    hideModal(modalName) {
      //Fecha o Modal
      this.$root.$emit('bv::hide::modal', modalName, '#btnShow')
    }
  },
  created() { 
    //Traz os canais disponiveis
    axios
      .get('/api/management/channel/fetch-channels-by-status/A')
      .then(response => {
        this.channels = response.data.channels
      });
    
    //Traz os contatos
    axios
      .get('/api/calendar/fetch-contacts/')
      .then(response => {
        this.guestsOptions = response.data.contacts
      })

    //Traz os departamentos cadastrados
    axios
      .get('/api/management/department/fetch-departments/')
      .then(response => {
        this.departments = response.data.departments
      })
  },
  setup(props,{ emit }) {

    const blankContact = {
      'phoneNumber': '',
      'con_name': '',
      'email': '',
      'gender': [],
      'birthday': '',
    }
    const contactData = ref(JSON.parse(JSON.stringify(blankContact)))

    //Limpa os dados do popup
    const clearContactData = () => {
      contactData.value = JSON.parse(JSON.stringify(blankContact))
    }

    const localDepartmentSelected = ref()
    const errorMessage = ref(false)

    const {
      serviceLocal,
      resetTransferLocal,

      // UI
      onSubmit,
    } = useServiceModalAddServiceHandler(toRefs(props), emit)
    
    const {
      refFormObserver,
      getValidationState,
      resetForm,
      clearForm,
    } = formValidation(resetTransferLocal, props.clearServiceData)
    
    const {
      fetchContacts,
      searchQuery,
      contactsSearch,
      addContact
    } = useContactList()

    const {
      departmentSelected,
      departmentUsers
    } = useDepartmentUsersList()

    fetchContacts()

    const fuseSearch = (search, loading) => {
      searchQuery.value = search
    }
    
    watch(localDepartmentSelected, (value) => {
      serviceLocal.value.department = value
      departmentSelected.value = value
    })

    const checkGuestExist = (GuestList) => {
      console.log(GuestList)

      // Filtra todos os contatos que estao em atendimento
      let contactsInService = GuestList.filter((contact) => {
        return contact.service
      })

      errorMessage.value = contactsInService.length > 0 ? true : false
    }

    return {
      // Add New Service
      serviceLocal,
      resetTransferLocal,
      onSubmit,

      refFormObserver,
      getValidationState,
      resetForm,
      clearForm,
      
      checkGuestExist,
      errorMessage,

      localDepartmentSelected,
      contactsSearch,
      searchQuery,
      fuseSearch,
      departmentUsers,

      contactData,
      clearContactData,
      addContact,
    }
  },
}
</script>