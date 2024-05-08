import { ref, computed, watch } from '@vue/composition-api'

export default function useCalendarEventHandler(props, emit) {
  // ------------------------------------------------
  // eventLocal
  // ------------------------------------------------
  const settingsLocal = ref(JSON.parse(JSON.stringify(props.campaign.value)))
  
  const resetUserLocal = () => {
    settingsLocal.value = JSON.parse(JSON.stringify(props.campaign.value))
  }
  watch(props.campaign, () => {
    resetUserLocal()
  })

  const onSubmit = () => {
    const settingsData = JSON.parse(JSON.stringify(settingsLocal))
    //Manda os dados para serem processados
    emit('update-operating-hours', settingsData.value)
  }

  return {
    settingsLocal,

    resetUserLocal,

    onSubmit,
  }
}
