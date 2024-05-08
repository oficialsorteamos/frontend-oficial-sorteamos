import { ref, computed, watch } from '@vue/composition-api'

export default function useCalendarEventHandler(props) {
  // ------------------------------------------------
  // eventLocal
  // ------------------------------------------------
  const userLocal = ref(JSON.parse(JSON.stringify(props.genderData.value)))
  
  var series = [
    {
      name: 'Visit source',
      type: 'pie',
      radius: ['50%', '70%'],
      avoidLabelOverlap: false,
      label: {
        show: true,
      },
      emphasis: {
        label: {
          show: true,
          fontSize: '40',
          fontWeight: 'bold'
        }
      },
      labelLine: {
        show: true,
      },
      data: [
        { value: 335, name: 'Point One' },
        { value: 310, name: 'Point Two' },
        { value: 234, name: 'Point Three' },
        { value: 435, name: 'Point Four' },
      ],
    },
  ]

  const resetUserLocal = () => { 

    userLocal.value = JSON.parse(JSON.stringify(props.genderData.value))
    userLocal.value.gendersName = series

    console.log('teste')
    console.log(userLocal)
  }
  watch(props.genderData, () => {
    resetUserLocal()
  })

  return {
    userLocal,
  }
}
