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
    const userData = JSON.parse(JSON.stringify(settingsLocal))
    //Manda os dados para serem processados
    emit('update-forwarding', userData.value)
  }

  return {
    settingsLocal,

    resetUserLocal,

    onSubmit,
  }
}
