import { ref, computed, watch } from '@vue/composition-api'
import { formatDateOnlyNumber } from '@core/utils/filter'

export default function useCalendarEventHandler(props, emit) {
  // ------------------------------------------------
  // eventLocal
  // ------------------------------------------------
  const contactLocal = ref(JSON.parse(JSON.stringify(props.contact.value)))

  contactLocal.value.birthday = formatDateOnlyNumber(contactLocal.value.birthday)
  
  const resetContactLocal = () => {
    contactLocal.value = JSON.parse(JSON.stringify(props.user.value))
  }
  watch(props.user, () => {
    resetContactLocal()
  })

  const onSubmit = () => {
    const contactData = JSON.parse(JSON.stringify(contactLocal))
    //Manda os dados para serem processados
    emit('update-contact-general', contactData.value)
    //Fecha o modal
    //emit('hide-modal', 'modal-edit-user-information')
  }

  return {
    contactLocal,

    resetContactLocal,

    onSubmit,
  }
}
