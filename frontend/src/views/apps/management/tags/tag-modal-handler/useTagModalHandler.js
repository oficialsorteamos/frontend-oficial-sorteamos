import { ref, computed, watch } from '@vue/composition-api'

export default function useDepartmentModalHandler(props, emit) {
  // ------------------------------------------------
  // eventLocal
  // ------------------------------------------------
  const tagLocal = ref(JSON.parse(JSON.stringify(props.tag.value)))
  
  const resetTagLocal = () => {
    tagLocal.value = JSON.parse(JSON.stringify(props.tag.value))
  }
  watch(props.tag, () => {
    resetTagLocal()
  })

  const onSubmit = () => {
    const tagData = JSON.parse(JSON.stringify(tagLocal))
    //Manda os dados para serem processados
    if (props.tag.value.id) emit('update-tag', tagData.value)
    else emit('add-tag', tagData.value)

    //Fecha o modal associado a um canal específico
    emit('hide-modal', 'modal-edit-tag-'+props.tag.value.id)
    emit('hide-modal', 'modal-add-tag')
  }

  return {
    tagLocal,

    resetTagLocal,

    onSubmit,
  }
}
