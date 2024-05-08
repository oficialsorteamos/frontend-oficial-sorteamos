<template>
  <b-card
    v-if="tableData"
    class="card-company-table"
  >
    <b-card-title class="mb-1">
      {{ $t('dashboard.operatorRanking.operatorRanking') }}
    </b-card-title>
    <b-card-sub-title class="mb-2">
      {{ $t('dashboard.operatorRanking.topOperators') }}
    </b-card-sub-title>

    <b-table
      :items="tableData"
      responsive
      :fields="fields"
      class="mb-0"
    >
      <template #cell(company)="data">
        <div class="d-flex align-items-center">
          <b-avatar
              rounded
              size="32"
              variant="light-company"
              v-if="data.index == 0"
            >
            <b-img
              :src="require('@/assets/images/illustration/badge.svg')"
              style="width: 16px"
              alt="avatar img"
              
            />
          </b-avatar>
          <b-avatar
              size="32"
              variant="light-company"
              v-else
              style="background-color: white;"
            >
          </b-avatar>
          <b-media>
            <template #aside>
              <b-avatar
                size="32"
                :src="data.item.avatar"
                :text="avatarText(data.item.name)"
                variant="light-info"
              />
            </template>
            <b-link
              :to="{ name: 'apps-management-users-view', params: { id: data.item.id } }"
              class="font-weight-bold d-block text-nowrap"
              v-b-tooltip.hover.v-secondary
              :title="data.item.name.length > 13? data.item.name : null"
            >
              {{ data.item.name.length > 13? data.item.name.substring(0, 13)+'...' : data.item.name }}
            </b-link>
            <div class="font-small-2 text-muted" style="text-align: left !important">
              <b-form-rating
                id="rating-sm-no-border"
                v-model="data.item.rating"
                no-border
                variant="warning"
                inline
                size="sm"
                v-b-tooltip.hover.v-secondary
                :title="data.item.rating"
                readonly
                style="margin-left: -15px; text-align: left !important"
              />
            </div>
          </b-media>
        </div>
      </template>
      
      <!-- views -->
      <template #cell(views)="data">
        <div class="d-flex flex-column">
          <span class="font-weight-bolder mb-25">{{ data.item.countServices }}</span>
          <!--
          <span class="font-small-2 text-muted text-nowrap">Services</span>
          -->
        </div>
      </template>

      <!-- revenue -->
      <template #cell(revenue)="data">
        {{ '$'+data.item.revenue }}
      </template>

      <!-- sales -->
      <template #cell(sales)="data">
        <div class="d-flex align-items-center">
          <span class="font-weight-bolder mr-1">{{ data.item.sales+'%' }}</span>
          <feather-icon
            :icon="data.item.loss ? 'TrendingDownIcon':'TrendingUpIcon'"
            :class="data.item.loss ? 'text-danger':'text-success'"
          />
        </div>
      </template>
    </b-table>
  </b-card>
</template>

<script>
import {
  BCard, BTable, BAvatar, BImg, BFormRating, VBTooltip, BCardTitle, BLink, BMedia, BCardSubTitle,
} from 'bootstrap-vue'
import { avatarText } from '@core/utils/filter'

export default {
  components: {
    BCard,
    BTable,
    BAvatar,
    BImg,
    BFormRating,
    BCardTitle,
    BLink,
    BMedia,
    BCardSubTitle,
  },
  directives: {
    'b-tooltip': VBTooltip,
  },
  props: {
    tableData: {
      type: Array,
      default: () => [],
    },
  },

  data() {
    return {
      fields: [
        { key: 'company', label: 'Operadores', class: 'text-center' },
        { key: 'views', label: 'Nº Atendimentos', class: 'text-center' },
      ],
    }
  },
  setup() {

    return {
      avatarText
    }
  }
}
</script>

<style lang="scss" scoped>
@import '~@core/scss/base/bootstrap-extended/include';
@import '~@core/scss/base/components/variables-dark';

.card-company-table ::v-deep td .b-avatar.badge-light-company {
  .dark-layout & {
    background: $theme-dark-body-bg !important;
  }
}
</style>
