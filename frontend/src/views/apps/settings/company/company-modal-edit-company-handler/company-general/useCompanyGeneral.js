import { ref, computed, watch } from '@vue/composition-api'
import { formatDateOnlyNumber } from '@core/utils/filter'

export default function useCalendarEventHandler(props, emit) {
  // ------------------------------------------------
  // eventLocal
  // ------------------------------------------------
  const companyLocal = ref(JSON.parse(JSON.stringify(props.company.value)))
  
  const resetCompanyLocal = () => {
    companyLocal.value = JSON.parse(JSON.stringify(props.company.value))
  }
  watch(props.user, () => {
    resetCompanyLocal()
  })

  const onSubmit = () => {
    const companyData = JSON.parse(JSON.stringify(companyLocal))
    //Manda os dados para serem processados
    emit('update-company-general', companyData.value)
    //Fecha o modal
    //emit('hide-modal', 'modal-edit-user-information')
  }

  return {
    companyLocal,

    resetCompanyLocal,

    onSubmit,
  }
}
