<template>
  <b-card>
    <b-form
      enctype="multipart/form-data"
      @submit.prevent="onSubmit"
    >
        <div 
          class="align-self-center border rounded pt-2 pl-2 mb-3"
        >
          <b-row>
            <b-col
              cols="12"
            >
              <h5>
                {{ $t('campaign.campaignSettingOperatingHour.campaignOperatingMonday') }}
              </h5>
            </b-col>
          </b-row>
          <b-row>
            <b-col
              cols="3"
              class="mb-2"
            >
              <!-- Start Time -->
              <validation-provider
                #default="validationContext"
                :name="$t('campaign.campaignSettingOperatingHour.startTime')"
                rules="required|min:5"
              >
                <b-form-group
                  :label="$t('campaign.campaignSettingOperatingHour.startTime')"
                  label-for="task-title"
                >
                  
                  <b-form-input
                    id="contact-name"
                    v-model="settingsLocal.operating_hours[0].ope_hr_start"
                    :state="getValidationState(validationContext)"
                    trim
                    placeholder="00:00"
                    type="text"
                    :maxlength="5"
                    autocomplete="off"
                    v-mask="timeRangeMask"
                  />

                  <b-form-invalid-feedback>
                    {{ validationContext.errors[0] }}
                  </b-form-invalid-feedback>
                </b-form-group>
              </validation-provider>
            </b-col>
            <b-col
              cols="3"
              class="mt-2 pl-5"
            >
            {{ $t('campaign.campaignSettingOperatingHour.at') }}
            </b-col>
            <b-col
              cols="3"
              class="mb-2"
            >
              <!-- End Time -->
              <validation-provider
                #default="validationContext"
                :name="$t('campaign.campaignSettingOperatingHour.endTime')"
                rules="required|min:5"
              >
                <b-form-group
                  :label="$t('campaign.campaignSettingOperatingHour.endTime')"
                  label-for="task-title"
                >
                  <b-form-input
                    id="contact-name"
                    v-model="settingsLocal.operating_hours[0].ope_hr_end"
                    :state="getValidationState(validationContext)"
                    trim
                    placeholder="00:00"
                    type="text"
                    :maxlength="5"
                    autocomplete="off"
                    v-mask="timeRangeMask"
                  />

                  <b-form-invalid-feedback>
                    {{ validationContext.errors[0] }}
                  </b-form-invalid-feedback>
                </b-form-group>
              </validation-provider>
            </b-col>
          </b-row>
        </div>

        <div 
          class="align-self-center border rounded pt-2 pl-2 mb-3"
        >
          <b-row>
            <b-col
              cols="12"
            >
              <h5>
                {{ $t('campaign.campaignSettingOperatingHour.campaignOperatingSaturday') }}
              </h5>
            </b-col>
          </b-row>
          <b-row>
            <b-col
              cols="3"
              class="mb-2"
            >
              <!-- Start Time -->
              <validation-provider
                #default="validationContext"
                :name="$t('campaign.campaignSettingOperatingHour.startTime')"
                rules="required|min:5"
              >
                <b-form-group
                  :label="$t('campaign.campaignSettingOperatingHour.startTime')"
                  label-for="task-title"
                >
                  
                  <b-form-input
                    id="contact-name"
                    v-model="settingsLocal.operating_hours[1].ope_hr_start"
                    :state="getValidationState(validationContext)"
                    trim
                    placeholder="00:00"
                    type="text"
                    :maxlength="5"
                    autocomplete="off"
                    v-mask="timeRangeMask"
                  />

                  <b-form-invalid-feedback>
                    {{ validationContext.errors[0] }}
                  </b-form-invalid-feedback>
                </b-form-group>
              </validation-provider>
            </b-col>
            <b-col
              cols="3"
              class="mt-2 pl-5"
            >
            {{ $t('campaign.campaignSettingOperatingHour.at') }}
            </b-col>
            <b-col
              cols="3"
              class="mb-2"
            >
              <!-- End Time -->
              <validation-provider
                #default="validationContext"
                :name="$t('campaign.campaignSettingOperatingHour.endTime')"
                rules="required|min:5"
              >
                <b-form-group
                  :label="$t('campaign.campaignSettingOperatingHour.endTime')"
                  label-for="task-title"
                >
                  <b-form-input
                    id="contact-name"
                    v-model="settingsLocal.operating_hours[1].ope_hr_end"
                    :state="getValidationState(validationContext)"
                    trim
                    placeholder="00:00"
                    type="text"
                    :maxlength="5"
                    autocomplete="off"
                    v-mask="timeRangeMask"
                  />

                  <b-form-invalid-feedback>
                    {{ validationContext.errors[0] }}
                  </b-form-invalid-feedback>
                </b-form-group>
              </validation-provider>
            </b-col>
          </b-row>
        </div>

        <div 
          class="align-self-center border rounded pt-2 pl-2 mb-3"
        >
          <b-row>
            <b-col
              cols="12"
            >
              <h5>
                {{ $t('campaign.campaignSettingOperatingHour.campaignOperatingSunday') }}
              </h5>
            </b-col>
          </b-row>
          <b-row>
            <b-col
              cols="3"
              class="mb-2"
            >
              <!-- Start Time -->
              <validation-provider
                #default="validationContext"
                :name="$t('campaign.campaignSettingOperatingHour.startTime')"
                rules="required|min:5"
              >
                <b-form-group
                  :label="$t('campaign.campaignSettingOperatingHour.startTime')"
                  label-for="task-title"
                >
                  
                  <b-form-input
                    id="contact-name"
                    v-model="settingsLocal.operating_hours[2].ope_hr_start"
                    :state="getValidationState(validationContext)"
                    trim
                    placeholder="00:00"
                    type="text"
                    :maxlength="5"
                    autocomplete="off"
                    v-mask="timeRangeMask"
                  />

                  <b-form-invalid-feedback>
                    {{ validationContext.errors[0] }}
                  </b-form-invalid-feedback>
                </b-form-group>
              </validation-provider>
            </b-col>
            <b-col
              cols="3"
              class="mt-2 pl-5"
            >
            {{ $t('campaign.campaignSettingOperatingHour.at') }}
            </b-col>
            <b-col
              cols="3"
              class="mb-2"
            >
              <!-- End Time -->
              <validation-provider
                #default="validationContext"
                :name="$t('campaign.campaignSettingOperatingHour.endTime')"
                rules="required|min:5"
              >
                <b-form-group
                  :label="$t('campaign.campaignSettingOperatingHour.endTime')"
                  label-for="task-title"
                >
                  <b-form-input
                    id="contact-name"
                    v-model="settingsLocal.operating_hours[2].ope_hr_end"
                    :state="getValidationState(validationContext)"
                    trim
                    placeholder="00:00"
                    type="text"
                    :maxlength="5"
                    autocomplete="off"
                    v-mask="timeRangeMask"
                  />

                  <b-form-invalid-feedback>
                    {{ validationContext.errors[0] }}
                  </b-form-invalid-feedback>
                </b-form-group>
              </validation-provider>
            </b-col>
          </b-row>
        </div>

        <b-row>
        <!-- buttons -->
        <b-col cols="12">
          <b-button
            v-ripple.400="'rgba(255, 255, 255, 0.15)'"
            variant="primary"
            class="mr-1 mt-1"
            type="submit"
          >
            {{ $t('campaign.campaignSettingOperatingHour.update') }}
          </b-button>
        </b-col>
        <!--/ buttons -->
      </b-row>
    </b-form>
  </b-card>
</template>

<script>
import {
  BButton, BRow, BCol, BCard, BFormCheckbox, BForm, BFormGroup, BFormInput, BFormInvalidFeedback,
} from 'bootstrap-vue'
import { toRefs } from '@vue/composition-api'
import Ripple from 'vue-ripple-directive'
import { ValidationProvider, ValidationObserver } from 'vee-validate'
import formValidation from '@core/comp-functions/forms/form-validation'
import useCampaignSettingOperatingHour from './useCampaignSettingOperatingHour'
import { VueMaskDirective } from 'v-mask'
import Vue from 'vue'
Vue.directive('mask', VueMaskDirective)

export default {
  components: {
    BButton,
    BRow,
    BCol,
    BCard,
    BFormCheckbox,
    BForm,
    BFormGroup,
    BFormInput,
    BFormInvalidFeedback,

    // Form Validation
    ValidationProvider,
    ValidationObserver,

    //Máscara
    VueMaskDirective,
  },
  directives: {
    Ripple,
  },
  props: {
    campaign: {
      type: Object,
      required: true,
    },
  },
  data: () => ({
    timeRangeMask,
  }),
  setup(props, {emit}) {
    const {
      settingsLocal,
      resetUserLocal,
      // UI
      onSubmit,
    } = useCampaignSettingOperatingHour(toRefs(props), emit)

    const {
      refFormObserver,
      getValidationState,
    } = formValidation(resetUserLocal, props.clearContactData)

    return {
      settingsLocal,
      resetUserLocal,
      // UI
      onSubmit,

      refFormObserver,
      getValidationState,
    }
  }
}

/**
   * Generate a time mask based on input value (23:59)
   * @param {string} value
   */
  export function timeMask(value) {
    const hours = [
      /[0-2]/,
      value.charAt(0) === '2' ? /[0-3]/ : /[0-9]/,
    ];
    const minutes = [/[0-5]/, /[0-9]/];
    return value.length > 2
      ? [...hours, ':', ...minutes]
      : hours;
  }

  /**
   * Generate a time range mask based on input value (00:00-23:59)
   * @param {string} value
   */
  export function timeRangeMask(value) {
    const numbers = value.replace(/[^0-9]/g, '');
    if (numbers.length > 4) {
      return [...timeMask(numbers.substring(0, 4)), '-', ...timeMask(numbers.substring(4))];
    }
    return [...timeMask(numbers)];
  }
</script>
