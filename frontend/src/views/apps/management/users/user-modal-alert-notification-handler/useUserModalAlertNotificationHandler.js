import { ref, computed, watch } from '@vue/composition-api'

export default function useCalendarEventHandler(props, emit) {
  // ------------------------------------------------
  // eventLocal
  // ------------------------------------------------
  const alertLocal = ref(JSON.parse(JSON.stringify(props.user.value)))
  
  const resetAlertLocal = () => {
    alertLocal.value = JSON.parse(JSON.stringify(props.user.value))
  }
  watch(props.user, () => {
    resetAlertLocal()
  })

  const onSubmit = () => {
    const alertData = JSON.parse(JSON.stringify(alertLocal))
    //Manda os dados para serem processados
    emit('send-alert-notification', alertData.value)
    //Limpa o campo de texto da mensagem
    alertLocal.value.message = ''
  }

  return {
    alertLocal,

    resetAlertLocal,

    onSubmit,
  }
}
