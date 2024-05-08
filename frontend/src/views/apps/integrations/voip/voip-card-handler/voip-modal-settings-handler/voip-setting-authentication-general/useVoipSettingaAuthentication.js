import { ref, computed, watch } from '@vue/composition-api'

export default function useCalendarEventHandler(props, emit) {
  // ------------------------------------------------
  // eventLocal
  // ------------------------------------------------
  const settingsLocal = ref(JSON.parse(JSON.stringify(props.voip.value)))
  if(settingsLocal.value.setting == null) {
    settingsLocal.value.setting = JSON.parse(JSON.stringify({voi_user: '', voi_secret: ''})) 
  }
  
  const resetUserLocal = () => {
    settingsLocal.value = JSON.parse(JSON.stringify(props.voip.value))
  }
  watch(props.voip, () => {
    resetUserLocal()
  })

  const onSubmit = () => {
    const settingsData = JSON.parse(JSON.stringify(settingsLocal))
    //Manda os dados para serem processados
    emit('update-authentication', settingsData.value)
    //Fecha o modal
    emit('hide-modal', 'modal-edit-user-information')
  }

  return {
    settingsLocal,

    resetUserLocal,

    onSubmit,
  }
}
