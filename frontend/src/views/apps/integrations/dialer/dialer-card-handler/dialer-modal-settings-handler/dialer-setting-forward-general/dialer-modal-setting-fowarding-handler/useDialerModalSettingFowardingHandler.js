import { ref, computed, watch } from '@vue/composition-api'

export default function useCalendarEventHandler(props, emit) {
  // ------------------------------------------------
  // eventLocal
  // ------------------------------------------------
  const settingsLocal = ref(JSON.parse(JSON.stringify(props.fowardingSetting.value)))
  
  
  const resetTransferLocal = () => {
    settingsLocal.value = JSON.parse(JSON.stringify(props.fowardingSetting.value))
  }
  watch(props.fowardingSetting, () => {
    resetTransferLocal()
  })

  const onSubmit = () => {
    const fowardingSettingData = JSON.parse(JSON.stringify(settingsLocal))
    if (props.fowardingSetting.value.id) emit('update-fowarding-setting', fowardingSettingData.value)
    else emit('add-fowarding-setting', fowardingSettingData.value)

    //Fecha o modal
    emit('hide-modal', 'modal-settings-fowarding')
    emit('hide-modal', 'modal-settings-fowarding-'+fowardingSettingData.value.id)
  }

  return {
    settingsLocal,

    onSubmit,
  }
}
