<template>
  <div>
    <template>
      <!-- Header -->
      <div class="d-flex justify-content-between align-items-center content-sidebar-header px-2 py-1">
        <h5 class="mb-0">
          {{ eventLocal.id ? $t('calendar.calendarEventHandler.update'): $t('calendar.calendarEventHandler.add') }} {{ $t('calendar.calendarEventHandler.event') }}
        </h5>
      </div>

      <!-- Body -->
      <validation-observer
        #default="{ handleSubmit }"
        ref="refFormObserver"
      >
        <!-- Form -->
        <b-form
          class="p-2"
          @submit.prevent="handleSubmit(onSubmit)"
          @reset.prevent="resetForm"
        >
          <input
            type="hidden"
            id="modalForm"
            v-bind:value="eventLocal.modal=true"
          />
          <input
            type="hidden"
            id="contactId"
            v-bind:value="eventLocal.contactId=contactId"
          />

          <!-- Title -->
          <validation-provider
            #default="validationContext"
            :name="$t('calendar.calendarEventHandler.title')"
            rules="required"
          >
            <b-form-group
              :label="$t('calendar.calendarEventHandler.title')"
              label-for="event-title"
            >
              <b-form-input
                id="event-title"
                v-model="eventLocal.title"
                autofocus
                :state="getValidationState(validationContext)"
                trim
                :placeholder="$t('calendar.calendarEventHandler.titlePlaceholder')"
              />

              <b-form-invalid-feedback>
                {{ validationContext.errors[0] }}
              </b-form-invalid-feedback>
            </b-form-group>
          </validation-provider>

          <!-- Calendar -->
            <validation-provider
              #default="validationContext"
              :name="$t('calendar.calendarEventHandler.tag')"
              rules="required"
            >

              <b-form-group
                :label="$t('calendar.calendarEventHandler.tag')"
                label-for="calendar"
                :state="getValidationState(validationContext)"
              >
                <v-select
                  v-model="eventLocal.extendedProps.tag"
                  :dir="$store.state.appConfig.isRTL ? 'rtl' : 'ltr'"
                  :options="tagsCalendar"
                  label="label"
                  :getOptionLabel="tagsCalendar => tagsCalendar.tag_name"
                  input-id="calendar"
                >

                  <template #option="{ tag_color, tag_name }">
                    <div
                      class="rounded-circle d-inline-block mr-50"
                      :style="`background-color:${tag_color}`"
                      style="height:10px;width:10px"
                    />
                    <span> {{ tag_name }}</span>
                  </template>

                  <template #selected-option="{ tag_color, tag_name }">
                    <div
                      class="rounded-circle d-inline-block mr-50"
                      :style="`background-color:${tag_color}`"
                      style="height:10px;width:10px"
                    />
                    <span> {{ tag_name }}</span>
                  </template>
                </v-select>

                <b-form-invalid-feedback :state="getValidationState(validationContext)">
                  {{ validationContext.errors[0] }}
                </b-form-invalid-feedback>
              </b-form-group>
            </validation-provider>

          <!-- Start Date -->
          <validation-provider
            #default="validationContext"
            :name="$t('calendar.calendarEventHandler.startDate')"
            rules="required"
          >

            <b-form-group
              :label="$t('calendar.calendarEventHandler.startDate')"
              label-for="start-date"
              :state="getValidationState(validationContext)"
            >
              <flat-pickr
                v-model="eventLocal.start"
                class="form-control"
                :config="{ enableTime: true, dateFormat: 'Y-m-d H:i'}"
              />
              <b-form-invalid-feedback :state="getValidationState(validationContext)">
                {{ validationContext.errors[0] }}
              </b-form-invalid-feedback>
            </b-form-group>
          </validation-provider>

          <!-- End Date -->
          <validation-provider
            #default="validationContext"
            :name="$t('calendar.calendarEventHandler.endDate')"
            rules="required"
          >

            <b-form-group
              :label="$t('calendar.calendarEventHandler.endDate')"
              label-for="end-date"
              :state="getValidationState(validationContext)"
            >
              <flat-pickr
                v-model="eventLocal.end"
                class="form-control"
                :config="{ enableTime: true, dateFormat: 'Y-m-d H:i'}"
              />
              <b-form-invalid-feedback :state="getValidationState(validationContext)">
                {{ validationContext.errors[0] }}
              </b-form-invalid-feedback>
            </b-form-group>
          </validation-provider>

          <!-- Event URL -->
          <validation-provider
            #default="validationContext"
            :name="$t('calendar.calendarEventHandler.eventURL')"
            rules="url"
          >

            <b-form-group
              :label="$t('calendar.calendarEventHandler.eventURL')"
              label-for="event-url"
            >
              <b-input-group>
                <b-form-input
                  id="event-url"
                  v-model="eventLocal.url"
                  type="url"
                  :state="getValidationState(validationContext)"
                  placeholder="htttps://www.google.com"
                  trim
                />
                <b-input-group-append>
                  <b-button 
                    variant="outline-primary"
                    :href="eventLocal.url"
                    target="_blank"
                  >
                    <feather-icon
                      icon="ExternalLinkIcon"
                      size="14"
                    />
                  </b-button>
                </b-input-group-append>
              </b-input-group>
              <b-form-invalid-feedback :state="getValidationState(validationContext)">
                {{ validationContext.errors[0] }}
              </b-form-invalid-feedback>
            </b-form-group>
          </validation-provider>

          <!-- Textarea -->
          <b-form-group
            :label="$t('calendar.calendarEventHandler.description')"
            label-for="event-description"
          >
            <b-form-textarea
              id="event-description"
              v-model="eventLocal.extendedProps.description"
            />
          </b-form-group>

          <!-- Form Actions -->
          <div class="d-flex mt-2 modal-footer">
            <b-button
              v-ripple.400="'rgba(255, 255, 255, 0.15)'"
              variant="primary"
              class="mr-2"
              type="submit"
              @click="$emit('hideModal')"
            >
              {{ eventLocal.id ? $t('calendar.calendarEventHandler.update') : $t('calendar.calendarEventHandler.add') }}
            </b-button>
            <b-button
              v-ripple.400="'rgba(186, 191, 199, 0.15)'"
              type="reset"
              variant="outline-secondary"
            >
              {{ $t('calendar.calendarEventHandler.reset') }}
            </b-button>
          </div>
        </b-form>
      </validation-observer>
    </template>
  </div>
</template>

<script>
import {
  BSidebar, BForm, BFormGroup, BFormInput, BFormCheckbox, BAvatar, BFormTextarea, BButton, BFormInvalidFeedback, BInputGroupAppend, BInputGroup,
} from 'bootstrap-vue'
import vSelect from 'vue-select'
import flatPickr from 'vue-flatpickr-component'
import Ripple from 'vue-ripple-directive'
import { ValidationProvider, ValidationObserver } from 'vee-validate'
import { required, email, url } from '@validations'
import formValidation from '@core/comp-functions/forms/form-validation'
import { ref, toRefs } from '@vue/composition-api'
import useCalendarEventHandler from './useCalendarEventHandler'
import axios from '@axios'

export default {
  components: {
    BButton,
    BSidebar,
    BForm,
    BFormGroup,
    BFormInput,
    BFormCheckbox,
    BFormTextarea,
    BAvatar,
    BInputGroupAppend,
    BInputGroup,
    vSelect,
    flatPickr,
    ValidationProvider,
    BFormInvalidFeedback,
    ValidationObserver,
  },
  directives: {
    Ripple,
  },
  model: {
    prop: 'isEventHandlerSidebarActive',
    event: 'update:is-event-handler-sidebar-active',
  },
  props: {
    isEventHandlerSidebarActive: {
      type: Boolean,
      required: true,
    },
    event: {
      type: Object,
      required: true,
    },
    clearEventData: {
      type: Function,
      required: true,
    },
    contactId: {
      type: Number,
      required: false,
    }
  },
  methods:{
    hideModal() {
      this.$emit('hideModal')
      console.log('Emitindo a mensagem')
    }
  },
  data() {
    return {
      required,
      email,
      url,
      tagsCalendar: [],
    }
  },
  created() {     
    //Traz as tags cadastradas para a agenda
    axios
      .get('/api/management/tag/fetch-tags-type/2')
      .then(response => {
        this.tagsCalendar = response.data.tags
      })
  },
  setup(props, { emit }) {
    /*
     ? This is handled quite differently in SFC due to deadlock of `useFormValidation` and this composition function.
     ? If we don't handle it the way it is being handled then either of two composition function used by this SFC get undefined as one of it's argument.
     * The Trick:

     * We created reactive property `clearFormData` and set to null so we can get `resetEventLocal` from `useCalendarEventHandler` composition function.
     * Once we get `resetEventLocal` function which is required by `useFormValidation` we will pass it to `useFormValidation` and in return we will get `clearForm` function which shall be original value of `clearFormData`.
     * Later we just assign `clearForm` to `clearFormData` and can resolve the deadlock. 😎

     ? Behind The Scene
     ? When we passed it to `useCalendarEventHandler` for first time it will be null but right after it we are getting correct value (which is `clearForm`) and assigning that correct value.
     ? As `clearFormData` is reactive it is being changed from `null` to corrent value and thanks to reactivity it is also update in `useCalendarEventHandler` composition function and it is getting correct value in second time and can work w/o any issues.
    */
    const clearFormData = ref(null)

    const {
      eventLocal,
      resetEventLocal,
      calendarOptions,

      // UI
      onSubmit,
      guestsOptions,
    } = useCalendarEventHandler(toRefs(props), clearFormData, emit)

    const {
      refFormObserver,
      getValidationState,
      resetForm,
      clearForm,
    } = formValidation(resetEventLocal, props.clearEventData)

    clearFormData.value = clearForm

    return {
      // Add New Event
      eventLocal,
      calendarOptions,
      onSubmit,
      guestsOptions,

      // Form Validation
      resetForm,
      refFormObserver,
      getValidationState,
    }
  },
}
</script>

<style lang="scss">
@import '@core/scss/vue/libs/vue-select.scss';
@import '@core/scss/vue/libs/vue-flatpicker.scss';
</style>
