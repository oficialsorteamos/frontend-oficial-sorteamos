import { ref, computed, watch } from '@vue/composition-api'

export default function useCalendarEventHandler(props, emit) {
  // ------------------------------------------------
  // eventLocal
  // ------------------------------------------------
  const mailingLocal = ref(JSON.parse(JSON.stringify(props.mailing.value)))
  
  const resetTransferLocal = () => {
    mailingLocal.value = JSON.parse(JSON.stringify(props.mailing.value))
  }
  watch(props.mailing, () => {
    resetTransferLocal()
  })

  const onSubmit = () => {
    const mailingData = JSON.parse(JSON.stringify(mailingLocal))
    //Manda os dados para serem processados
    emit('add-mailing', mailingData.value)
    //Fecha o modal
    emit('hide-modal', 'modal-mailing-'+mailingData.value.campaignId)
  }

  return {
    mailingLocal,

    resetTransferLocal,

    onSubmit,
  }
}
