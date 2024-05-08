import { ref, computed, watch } from '@vue/composition-api'

export default function useCalendarEventHandler(props, emit) {
  // ------------------------------------------------
  // eventLocal
  // ------------------------------------------------
  const contactLocal = ref(JSON.parse(JSON.stringify(props.contact.value)))
  
  const resetContactLocal = () => {
    contactLocal.value = JSON.parse(JSON.stringify(props.contact.value))
  }

  const onSubmit = () => {
    const contactData = JSON.parse(JSON.stringify(contactLocal))

    //Manda os dados para serem processados
    if(props.addressId.value) {
      contactData.value.id = props.addressId.value
      emit('update-contact-address', contactData.value)
    }
    else {
      emit('add-contact-address', contactData.value)
    }
  }

  return {
    contactLocal,

    resetContactLocal,

    onSubmit,
  }
}
