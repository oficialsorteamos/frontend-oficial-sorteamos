<template>
  <b-card
    v-if="statisticsData"
    no-body
    class="card-statistics"
  >
    <b-card-header>
      <b-card-title>{{ $t('dashboard.generalStatistics.statistics') }}</b-card-title>
      <!--
      <b-card-text class="font-small-2 mr-25 mb-0">
        Updated 1 month ago
      </b-card-text>
      -->
    </b-card-header>
    <b-card-body class="statistics-body">
      <b-row>
        <b-col
          xl="3"
          sm="6"
          class="mb-2 mb-xl-0"
        >
          <b-media no-body>
            <b-media-aside

              class="mr-2"
            >
              <b-avatar
                size="48"
                variant="light-primary"
              >
                <feather-icon
                  size="24"
                  icon="TrendingUpIcon"
                />
              </b-avatar>
            </b-media-aside>
            <b-media-body class="my-auto">
              <h4 class="font-weight-bolder mb-0">
                {{statisticsData.serviceTotalAmounts}}
              </h4>
              <b-card-text class="font-small-3 mb-0">
                {{ $t('dashboard.generalStatistics.totalServices') }}
              </b-card-text>
            </b-media-body>
          </b-media>
        </b-col>
        <b-col
          xl="3"
          sm="6"
          class="mb-2 mb-xl-0"
        >
          <b-media no-body>
            <b-media-aside

              class="mr-2"
            >
              <b-avatar
                size="48"
                variant="light-secondary"
              >
                <feather-icon
                  size="24"
                  icon="PhoneIncomingIcon"
                />
              </b-avatar>
            </b-media-aside>
            <b-media-body class="my-auto">
              <h4 class="font-weight-bolder mb-0">
                {{ statisticsData.serviceOpenTotalAmounts }}
              </h4>
              <b-card-text class="font-small-3 mb-0">
                {{ $t('dashboard.generalStatistics.openServices') }}
              </b-card-text>
            </b-media-body>
          </b-media>
        </b-col>
        <b-col
          xl="3"
          sm="6"
          class="mb-2 mb-xl-0"
        >
          <b-media no-body>
            <b-media-aside

              class="mr-2"
            >
              <b-avatar
                size="48"
                variant="light-info"
              >
                <feather-icon
                  size="24"
                  icon="UserIcon"
                />
              </b-avatar>
            </b-media-aside>
            <b-media-body class="my-auto">
              <h4 class="font-weight-bolder mb-0">
                {{ statisticsData.contactTotalAmounts }}
              </h4>
              <b-card-text class="font-small-3 mb-0">
                {{ $t('dashboard.generalStatistics.contacts') }}
              </b-card-text>
            </b-media-body>
          </b-media>
        </b-col>
        <b-col
          xl="3"
          sm="6"
          class="mb-2 mb-xl-0"
        >
          <b-media no-body>
            <b-media-aside

              class="mr-2"
            >
              <b-avatar
                size="48"
                variant="light-success"
              >
                <feather-icon
                  size="24"
                  icon="RadioIcon"
                />
              </b-avatar>
            </b-media-aside>
            <b-media-body class="my-auto">
              <h4 class=" mb-0">
                <strong>{{ statisticsData.usersOnlineAmounts }}</strong>/<small>{{ statisticsData.usersActiveAmounts }}</small>
              </h4>
              <b-card-text class="font-small-3 mb-0">
                {{ $t('dashboard.generalStatistics.onlineOperators') }}
              </b-card-text>
            </b-media-body>
          </b-media>
        </b-col>
      </b-row>
    </b-card-body>
  </b-card>
</template>

<script>
import {
  BCard, BCardHeader, BCardTitle, BCardText, BCardBody, BRow, BCol, BMedia, BMediaAside, BAvatar, BMediaBody,
} from 'bootstrap-vue'

export default {
  components: {
    BRow,
    BCol,
    BCard,
    BCardHeader,
    BCardTitle,
    BCardText,
    BCardBody,
    BMedia,
    BAvatar,
    BMediaAside,
    BMediaBody,
  },
  props: {
    statisticsData: {
      //type: Array,
      required: true,
    },
  },
}
</script>
