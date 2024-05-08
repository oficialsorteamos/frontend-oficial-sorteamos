import { ref, computed, watch } from '@vue/composition-api'

export default function useCalendarEventHandler(props, emit) {
  // ------------------------------------------------
  // eventLocal
  // ------------------------------------------------
  const settingsLocal = ref(JSON.parse(JSON.stringify(props.chatbot.value)))
  
  const resetUserLocal = () => {
    settingsLocal.value = JSON.parse(JSON.stringify(props.chatbot.value))
  }
  watch(props.chatbot, () => {
    resetUserLocal()
  })

  const onSubmit = () => {
    const settingsData = JSON.parse(JSON.stringify(settingsLocal))
    //Manda os dados para serem processados
    emit('update-channel', settingsData.value)
    //Fecha o modal
    emit('hide-modal', 'modal-edit-user-information')
  }

  return {
    settingsLocal,

    resetUserLocal,

    onSubmit,
  }
}
