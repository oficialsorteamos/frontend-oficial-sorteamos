import { ref, computed, watch } from '@vue/composition-api'

export default function useCalendarEventHandler(props) {
  // ------------------------------------------------
  // eventLocal
  // ------------------------------------------------
  const userLocal = ref(JSON.parse(JSON.stringify(props.genderData.value)))
  
  var chartOptions = {
    legend: {
      show: true,
      position: 'bottom',
      fontSize: '14px',
      fontFamily: 'Montserrat',
    },
    colors: [
      '#ffe700',
      '#00d4bd',
      '#826bf8',
      '#2b9bf4',
    ],
    dataLabels: {
      enabled: true,
      formatter(val) {
        // eslint-disable-next-line radix
        return `${parseInt(val)}%`
      },
    },
    labels: [],
    plotOptions: {
      pie: {
        donut: {
          labels: {
            show: true,
            name: {
              fontSize: '2rem',
              fontFamily: 'Montserrat',
            },
            value: {
              fontSize: '1rem',
              fontFamily: 'Montserrat',
              formatter(val) {
                // eslint-disable-next-line radix
                return `${parseInt(val)}%`
              },
            },
            total: {
              show: false,
              fontSize: '1.5rem',
              label: 'Operational',
              formatter() {
                return '31%'
              },
            },
          },
        },
      },
    },
    responsive: [
      {
        breakpoint: 992,
        options: {
          chart: {
            height: 380,
          },
          legend: {
            position: 'bottom',
          },
        },
      },
      {
        breakpoint: 576,
        options: {
          chart: {
            height: 320,
          },
          plotOptions: {
            pie: {
              donut: {
                labels: {
                  show: true,
                  name: {
                    fontSize: '1.5rem',
                  },
                  value: {
                    fontSize: '1rem',
                  },
                  total: {
                    fontSize: '1.5rem',
                  },
                },
              },
            },
          },
          legend: {
            show: true,
          },
        },
      },
    ],
  }

  const resetUserLocal = () => { 

    userLocal.value = JSON.parse(JSON.stringify(props.genderData.value))
    chartOptions.labels = userLocal.value.gendersName.labels
    userLocal.value.gendersName = chartOptions 
  }
  watch(props.genderData, () => {
    resetUserLocal()
  })

  return {
    userLocal,
  }
}
