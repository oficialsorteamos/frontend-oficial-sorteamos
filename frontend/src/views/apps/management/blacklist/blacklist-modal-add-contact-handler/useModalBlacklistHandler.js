import { ref, computed, watch } from '@vue/composition-api'

export default function useCalendarEventHandler(props, emit) {
  // ------------------------------------------------
  // eventLocal
  // ------------------------------------------------
  const blacklistLocal = ref(JSON.parse(JSON.stringify(props.contactBlacklist.value)))
  
  const resetTransferLocal = () => {
    blacklistLocal.value = JSON.parse(JSON.stringify(props.contactBlacklist.value))
  }
  watch(props.contactBlacklist, () => {
    resetTransferLocal()
  })

  const onSubmit = () => {
    const blacklistData = JSON.parse(JSON.stringify(blacklistLocal))
    //Manda os dados para serem processados
    emit('add-contact-blacklist', blacklistData.value)
    //Fecha o modal
    emit('hide-modal', 'modal-add-contact-blacklist')
  }

  return {
    blacklistLocal,

    resetTransferLocal,

    onSubmit,
  }
}
