<template id="t">
  <div>
    <!-- A função handleSubmit só deixa a o formulário ser submetido (só chama a função onSubmit) caso todos os campos do form satisfação os os pré-requisitos -->
    <validation-observer
      #default="{ handleSubmit }"
      ref="refFormObserver"
    >
      <b-form
        enctype="multipart/form-data"
        @submit.prevent="handleSubmit(onSubmit)"
      >
        <input
          type="hidden"
          id="typeQuickMessageId"
          v-bind:value="newQuickMessageLocal.typeQuickMessageId = typeQuickMessageId"
        />
        <b-row>
          <b-col
            md="12"
            class="mb-1"
          >
            <!-- Title -->
            <validation-provider
              #default="validationContext"
              :name="$t('chat.chatModalNewQuickMessageHandler.title')"
              rules="required"
            >
              <b-form-group
                :label="$t('chat.chatModalNewQuickMessageHandler.title')"
                label-for="task-title"
              >
                <b-form-input
                  id="task-title"
                  v-model="newQuickMessageLocal.title"
                  autofocus
                  :state="getValidationState(validationContext)"
                  trim
                  :placeholder="$t('chat.chatModalNewQuickMessageHandler.titlePlaceholder')"
                  type="text"
                  :maxlength="30"
                  autocomplete="off"
                />

                <b-form-invalid-feedback>
                  {{ validationContext.errors[0] }} 
                </b-form-invalid-feedback>
              </b-form-group>
            </validation-provider>
          </b-col>
        </b-row>
        <!-- Tipo de formato de mensagem -->
        <b-row>
          <b-col
            md="6"
            class="mb-1"
          >
            <!-- Tipo de botões -->
            <b-form-group
              :label="$t('chat.chatModalNewTemplateMessageHandler.formatMessage')"
              label-for="vue-select"
            >
              <v-select
                id="vue-select"
                v-model="newQuickMessageLocal.typeFormatMessage"
                :dir="$store.state.appConfig.isRTL ? 'rtl' : 'ltr'"
                :selectable="(option) => !option.typ_description.includes('Áudio - Disponível apenas para mensagens de WhatsApp') && !option.typ_description.includes('Texto - Disponível apenas para mensagens de WhatsApp e SMS') && !option.typ_description.includes('Arquivo - Disponível apenas para mensagens de WhatsApp')"
                @input="checkTypeFormatSelected"
                :options="typeFormatMessages"
                :getOptionLabel="typeFormatMessages => typeFormatMessages.typ_description"
                transition=""
              />
            </b-form-group> 
          </b-col>  
        </b-row>
        <span
          v-if="newQuickMessageLocal.typeFormatMessage && newQuickMessageLocal.typeFormatMessage.id == 1"
        >
          <!-- Se NÃO for uma mensagem de SMS -->
          <span
            v-if="typeQuickMessageId != 4"
          >
            <!-- Cabeçalho -->
            <b-badge
              variant="success"
              class="badge-glow mt-1 mb-2 p-1"
            >
              <span
                style="font-size: 14px"
              >
                {{ $t('chat.chatModalNewTemplateMessageHandler.header') }}
              </span>
            </b-badge>
            <div
              class="w-100 border-success rounded mb-2 p-1"
            >
              <b-row>
                <b-col
                  md="6"
                  class="mb-1"
                >
                  <!-- Tipo de botões -->
                  <b-form-group
                    :label="$t('chat.chatModalNewTemplateMessageHandler.typeHeader')"
                    label-for="vue-select"
                  >
                    <v-select
                      id="vue-select"
                      v-model="newQuickMessageLocal.typeHeader"
                      :dir="$store.state.appConfig.isRTL ? 'rtl' : 'ltr'"
                      :options="typeParameters"
                      :getOptionLabel="typeParameters => typeParameters.qui_name"
                      transition=""
                      @input="clearHeaderData"
                    />
                  </b-form-group> 
                </b-col>  
              </b-row>
              <b-row>
                <b-col
                  md="8"
                  class="mb-1"
                >
                  <span
                    v-if="newQuickMessageLocal.typeHeader"
                  >
                    <!-- Se o tipo de cabeçalho for um TEXTO -->
                    <span
                      v-if="newQuickMessageLocal.typeHeader.id == 1"
                    >
                      <!-- Texto do cabeçalho -->
                      <validation-provider
                        #default="validationContext"
                        :name="$t('chat.chatModalNewTemplateMessageHandler.text')"
                        rules="required|min:1"
                      >
                        <b-form-group
                          :label="$t('chat.chatModalNewTemplateMessageHandler.text')"
                          label-for="task-title"
                        >
                          <b-form-input
                            id="task-title"
                            v-model="newQuickMessageLocal.header"
                            :state="getValidationState(validationContext)"
                            trim
                            :placeholder="$t('chat.chatModalNewTemplateMessageHandler.textPlaceholder')"
                            type="text"
                            :maxlength="60"
                            autocomplete="off"
                          />
                          <b-form-invalid-feedback>
                            {{ validationContext.errors[0] }}
                          </b-form-invalid-feedback>
                        </b-form-group>
                      </validation-provider>
                    </span>
                    <!-- Se for alguma MÍDIA no cabeçalho -->
                    <span
                      v-else
                    >
                      <!-- Mídia do cabeçalho -->
                      <validation-provider
                        #default="validationContext"
                        :name="$t('chat.chatModalNewTemplateMessageHandler.media')"
                        rules="required"
                      >
                        <b-form-group
                          label="Media"
                          label-for="task-title"
                        >
                          <b-form-file
                            v-model="fileSelected"
                            ref="importFileTemplate"
                            name="importFileTemplate"
                            id="importFileTemplate"
                            :state="getValidationState(validationContext)"
                            :accept="newQuickMessageLocal.typeHeader.id == 2? '.jpeg, .jpg, .png' : '.mp4'"
                            :placeholder="$t('chat.chatModalNewTemplateMessageHandler.filePlaceholder')"
                            :drop-placeholder="$t('chat.chatModalNewTemplateMessageHandler.filePlaceholder')"
                            @change="handleFileUpload"
                          />
                          <b-form-invalid-feedback>
                            {{ validationContext.errors[0] }}
                          </b-form-invalid-feedback>
                        </b-form-group>
                      </validation-provider>
                    </span>
                  </span>
                </b-col>
              </b-row>
            </div>
          </span>
          <!-- Corpo da Mensagem -->
          <b-badge
            variant="primary"
            class="badge-glow mt-1 mb-1 p-1"
          >
            <span
              style="font-size: 14px"
            >
              {{ $t('chat.chatModalNewTemplateMessageHandler.body') }}
            </span>
          </b-badge>
          <div
            class="w-100 border-primary rounded mb-2 p-1"
          >
            <b-row class="mb-2" 
            >
              <b-col
                :md="typeQuickMessageId != 4? 8 : 12"
                class="mb-1"
              >
                <!-- Content -->
                <b-form-group
                  :label="$t('chat.chatModalNewQuickMessageHandler.content')"
                  label-for="new-quick-message-content"
                >
                  <quill-editor
                    id="quil-content"
                    v-model="newQuickMessageLocal.content"
                    @change="countVariablesText();"
                    :options="editorOption"
                    class="border-bottom-0"
                    ref="myEditor"
                  />
                  <div
                    id="quill-toolbar"
                    class="d-flex justify-content-end border-top-0"
                  >
                    <twemoji-picker
                      :emojiData="emojiDataAll"
                      :emojiGroups="emojiGroups"
                      :skinsSelection="false"
                      :searchEmojisFeat="true"
                      :recentEmojisFeat="true"
                      :randomEmojiArray="['😀']"
                      searchEmojiPlaceholder="Search here."
                      searchEmojiNotFound="Emojis not found."
                      isLoadingLabel="Loading..."
                      @emojiUnicodeAdded="emojiSelected"
                      twemojiPath="https://cdnjs.cloudflare.com/ajax/libs/twemoji/14.0.2/"
                    >
                    </twemoji-picker>
                    <!-- Add a bold button -->
                    <button class="ql-bold" />
                    <!--
                    <button class="ql-italic" />
                    <button class="ql-underline" />
                    <button class="ql-align" />
                    <button class="ql-link" />
                    -->
                  </div>
                  <span
                    style="font-size: 0.857rem;"
                  >
                    <i>{{ totalCharactersBody }}/{{typeQuickMessageId != 4? 1024 : 160 }}</i>
                  </span>
                </b-form-group>
              </b-col>
              <b-col
                  md="4"
                  class="mb-1"
                  v-if="typeQuickMessageId != 4"
                >
                <!-- Tags -->
                <div class="justify-content-between">
                  <h6 class="section-label mb-1">
                    Tags
                  </h6>
                </div>
                <b-list-group class="list-group-labels">
                      <p class="mb-2">
                        <b-badge
                          variant="primary"
                          v-clipboard:copy="'%nome%'"
                          v-clipboard:success="onCopy"
                          v-clipboard:error="onError"
                          class="badge-glow pointer-cursor"
                        >
                          %nome%
                        </b-badge>
                        Nome do contato
                      </p>
                      <p class="mb-2">
                        <b-badge
                          variant="success"
                          v-clipboard:copy="'%operador%'"
                          v-clipboard:success="onCopy"
                          v-clipboard:error="onError"
                          class="badge-glow pointer-cursor"
                        >
                          %operador%
                        </b-badge>
                        Nome do operador
                      </p>
                      <p class="mb-2">
                        <b-badge
                          variant="danger"
                          v-clipboard:copy="'%protocolo%'"
                          v-clipboard:success="onCopy"
                          v-clipboard:error="onError"
                          class="badge-glow pointer-cursor"
                        >
                          %protocolo%
                        </b-badge>
                        Nº do protocolo
                      </p>
                      <p>
                        <b-badge
                          variant="warning"
                          v-clipboard:copy="'%saudacao%'"
                          v-clipboard:success="onCopy"
                          v-clipboard:error="onError"
                          class="badge-glow pointer-cursor"
                        >
                          %saudacao%
                        </b-badge>
                        Saudação de acordo com o horário do dia
                      </p>
                      <p v-if="typeQuickMessageId == 3">
                        <b-badge
                          variant="dark"
                          v-clipboard:copy="'%dados_adicionais%'"
                          v-clipboard:success="onCopy"
                          v-clipboard:error="onError"
                          class="badge-glow pointer-cursor"
                        >
                          %dados_adicionais%
                        </b-badge>
                        Dados que foram inseridos na planilha de importação
                      </p>
                    
                  </b-list-group>
              </b-col>  
            </b-row>
          </div>

          <!-- Se NÃO for uma mensagem de SMS -->
          <span
            v-if="typeQuickMessageId != 4"
          >
            <!-- Botões -->
            <b-badge
              variant="dark"
              class="badge-glow mt-4 mb-1 p-1"
            >
              <span
                style="font-size: 14px"
              >
                {{ $t('chat.chatModalNewTemplateMessageHandler.buttons') }}
              </span>
            </b-badge>
            <div
              class="w-100 border-dark rounded p-1"
            >
              <b-row>
                <b-col
                  md="6"
                  class="mb-1"
                >
                  <!-- Tipo de botões -->
                  <b-form-group
                    :label="$t('chat.chatModalNewTemplateMessageHandler.typeButton')"
                    label-for="vue-select"
                  >
                    <v-select
                      id="vue-select"
                      v-model="newQuickMessageLocal.typeButton"
                      :dir="$store.state.appConfig.isRTL ? 'rtl' : 'ltr'"
                      :options="typeButtons"
                      :getOptionLabel="typeButtons => typeButtons.tem_name"
                      :selectable="(option) => !option.tem_name.includes('Não é possível selecionar esse tipo de botão')"
                      transition=""
                      :disabled="templateButtons.length > 0? true : false"
                      @input="checkLimitButtons"
                    />
                  </b-form-group>
                </b-col>
                <b-col
                  md="3"
                  class="mb-1"
                >
                  <b-button
                    v-ripple.400="'rgba(255, 255, 255, 0.15)'"
                    variant="primary"
                    @click="repeateAgain"
                    class="mt-2"
                    :disabled="addButtonDisabled"
                    v-if="newQuickMessageLocal.typeButton && newQuickMessageLocal.typeButton.id != 3"
                  >
                    <feather-icon
                      icon="PlusIcon"
                      class="mr-25"
                    />
                    <span>{{ $t('chat.chatModalNewTemplateMessageHandler.add') }}</span>
                  </b-button>
                </b-col>
              </b-row>
              <b-form
                ref="form"
                :style="{height: trHeight}"
                class="repeater-form"
                @submit.prevent="repeateAgain"
              >
                <!-- Se for um botão do tipo RESPOSTA RÁPIDA -->
                <span
                  v-if="newQuickMessageLocal.typeButton? newQuickMessageLocal.typeButton.id == 1 : ''"
                >
                  <!-- Row Loop -->
                  <div
                    v-for="(item, index) in templateButtons"
                    :id="index"
                    :key="index"
                    ref="row"
                  >
                    <h5
                      style="font-size: 12px"
                    >
                      {{ $t('chat.chatModalNewTemplateMessageHandler.button') }} {{ index+1 }}
                    </h5>
                    <p
                      class="border-secondary rounded p-1"
                      style="width: 100%"
                    >
                      <b-button
                        v-ripple.400="'rgba(234, 84, 85, 0.15)'"
                        variant="danger"
                        class="btn-icon float-right"
                        @click="removeItem(index)"
                      >
                        <feather-icon
                          icon="TrashIcon"
                          size="16"
                        />
                      </b-button>
                      <b-row>
                        <!-- Item Name -->
                        <b-col md="10">
                          <validation-provider
                            #default="validationContext"
                            :name="$t('chat.chatModalNewTemplateMessageHandler.label')"
                            rules="required|min:1"
                          >
                            <b-form-group
                              :label="$t('chat.chatModalNewTemplateMessageHandler.label')"
                              label-for="item-name"
                            >
                              <b-form-input
                                v-model="newQuickMessageLocal.buttonLabel[index]"
                                id="item-name"
                                type="text"
                                :state="getValidationState(validationContext)"
                                trim
                                :maxlength="20"
                                autocomplete="off"
                                :placeholder="$t('chat.chatModalNewTemplateMessageHandler.buttonPlaceholder')"
                              />
                              <b-form-invalid-feedback>
                                {{ validationContext.errors[0] }}
                              </b-form-invalid-feedback>
                            </b-form-group>
                          </validation-provider>
                        </b-col>
                      </b-row>
                    </p>
                    <div>&nbsp;</div>
                  </div>
                </span>

                <!-- Se for um botão de CHAMADA PARA AÇÃO -->
                <span
                  v-if="newQuickMessageLocal.typeButton? newQuickMessageLocal.typeButton.id == 2 : ''"
                >
                  <!-- Row Loop -->
                  <div
                    v-for="(item, index) in templateButtons"
                    :id="index"
                    :key="index"
                    ref="row"
                  >
                    <h5
                      style="font-size: 12px"
                    >
                      {{ $t('chat.chatModalNewTemplateMessageHandler.button') }} {{ index+1 }}
                    </h5>
                    <p
                      class="border-secondary rounded p-1"
                      style="width: 100%"
                    >
                      <b-button
                        v-ripple.400="'rgba(234, 84, 85, 0.15)'"
                        variant="danger"
                        class="btn-icon float-right"
                        @click="removeItem(index)"
                      >
                        <feather-icon
                          icon="TrashIcon"
                          size="16"
                        />
                      </b-button>
                      <b-row>
                        <!-- Item Name -->
                        <b-col md="10" style="padding-left: 0px !important">
                            <!-- Tipo de botões -->
                          <b-form-group
                            :label="$t('chat.chatModalNewTemplateMessageHandler.callAction')"
                            label-for="vue-select"
                          >
                            <v-select
                              id="vue-select"
                              v-model="newQuickMessageLocal.callActions[index]"
                              :dir="$store.state.appConfig.isRTL ? 'rtl' : 'ltr'"
                              :options="typeCallActions"
                              :getOptionLabel="typeCallActions => typeCallActions.tem_name"
                              transition=""
                              :disabled="callActionDisabled"
                              @input="clearValuesButton(index)"
                            >
                              <template #search="{attributes, events}">
                                <input
                                  class="vs__search"
                                  :required="!newQuickMessageLocal.callActions[index]"
                                  v-bind="attributes"
                                  v-on="events"
                                />
                              </template>
                            </v-select>
                          </b-form-group>
                        </b-col>
                      </b-row>
                      <!-- Se a CHAMADA PARA AÇÃO for uma URL -->
                      <span
                        style="width: 100px"
                        v-if="newQuickMessageLocal.callActions[index].id == 1"
                      >
                        <b-row>
                          <!-- Nome do Botão -->
                          <b-col
                            md="6"
                          >
                            <validation-provider
                              #default="validationContext"
                              :name="$t('chat.chatModalNewTemplateMessageHandler.label')"
                              rules="required|min:1"
                            >
                              <b-form-group
                                label="Label"
                                label-for="task-title"
                              >
                                <b-form-input
                                  id="task-title"
                                  v-model="newQuickMessageLocal.buttonLabel[index]"
                                  autofocus
                                  :state="getValidationState(validationContext)"
                                  trim
                                  :placeholder="$t('chat.chatModalNewTemplateMessageHandler.buttonPlaceholder')"
                                  type="text"
                                  :maxlength="20"
                                  autocomplete="off"
                                />
                                <b-form-invalid-feedback>
                                  {{ validationContext.errors[0] }}
                                </b-form-invalid-feedback>
                              </b-form-group>
                            </validation-provider> 
                          </b-col>

                          <!-- URL -->
                          <b-col
                            md="6"
                          >
                            <validation-provider
                              #default="validationContext"
                              :name="$t('chat.chatModalNewTemplateMessageHandler.url')"
                              rules="required|url"
                            >
                              <b-form-group
                                :label="$t('chat.chatModalNewTemplateMessageHandler.url')"
                                label-for="task-title"
                              >
                                <b-form-input
                                  id="task-title"
                                  v-model="newQuickMessageLocal.buttonUrl"
                                  :state="getValidationState(validationContext)"
                                  trim
                                  :placeholder="$t('chat.chatModalNewTemplateMessageHandler.urlPlaceholder')"
                                  type="text"
                                  autocomplete="off"
                                />
                                <b-form-invalid-feedback>
                                  {{ validationContext.errors[0] }}
                                </b-form-invalid-feedback>
                              </b-form-group>
                            </validation-provider> 
                          </b-col>
                        </b-row>
                      </span>
                      <!-- Se a CHAMADA PARA AÇÃO for um número de telefone -->
                      <span
                        style="width: 100px"
                        v-if="newQuickMessageLocal.callActions[index].id == 2"
                      >
                        <b-row>
                          <!-- Nome do Botão -->
                          <b-col
                            md="6"
                          >
                            <validation-provider
                              #default="validationContext"
                              :name="$t('chat.chatModalNewTemplateMessageHandler.label')"
                              rules="required|min:1"
                            >
                              <b-form-group
                                label="Label"
                                label-for="task-title"
                              >
                                <b-form-input
                                  id="task-title"
                                  v-model="newQuickMessageLocal.buttonLabel[index]"
                                  autofocus
                                  :state="getValidationState(validationContext)"
                                  trim
                                  :placeholder="$t('chat.chatModalNewTemplateMessageHandler.buttonPlaceholder')"
                                  type="text"
                                  :maxlength="20"
                                  autocomplete="off"
                                />
                                <b-form-invalid-feedback>
                                  {{ validationContext.errors[0] }}
                                </b-form-invalid-feedback>
                              </b-form-group>
                            </validation-provider> 
                          </b-col>

                          <!-- URL -->
                          <b-col
                            md="6"
                          >
                            <validation-provider
                              #default="{ errors }"
                              :name="$t('chat.chatModalNewTemplateMessageHandler.phoneNumber')"
                              rules="required|min:12"
                            >
                              <b-form-group
                                label-for="user-phone"
                                :label="$t('chat.chatModalNewTemplateMessageHandler.phoneNumber')+'*'"
                              >
                                <!-- Phone Number -->
                                <VuePhoneNumberInput  
                                  v-model="newQuickMessageLocal.buttonPhone"
                                  :required="true"
                                  class="mb-1"
                                  @update="setPhoneNumber"
                                  default-country-code="BR"
                                />
                                <b-form-invalid-feedback :state="errors.length > 0 ? false:null">
                                  {{ errors[0] }}
                                </b-form-invalid-feedback>
                              </b-form-group>
                            </validation-provider>
                            <input
                              type="hidden"
                              id="phoneNumber"
                              v-bind:value="newQuickMessageLocal.phoneNumber = phoneNumber"
                            />
                          </b-col>
                        </b-row>
                      </span>
                    </p>
                    <div>&nbsp;</div>
                  </div>
                </span>

                <!-- Se for uma LISTA -->
                <span
                  v-if="newQuickMessageLocal.typeButton? newQuickMessageLocal.typeButton.id == 3 : ''"
                >
                  <b-row>
                    <!-- Item Name -->
                    <b-col md="8">
                      <validation-provider
                        #default="validationContext"
                        :name="$t('chat.chatModalNewTemplateMessageHandler.listLabel')"
                        rules="required|min:1"
                      >
                        <b-form-group
                          :label="$t('chat.chatModalNewTemplateMessageHandler.listLabel')+'*'"
                          label-for="item-name"
                        >
                          <b-form-input
                            v-model="newQuickMessageLocal.listLabel"
                            id="item-name"
                            type="text"
                            :state="getValidationState(validationContext)"
                            trim
                            :maxlength="20"
                            autocomplete="off"
                            :placeholder="$t('chat.chatModalNewTemplateMessageHandler.listPlaceholder')"
                          />
                          <b-form-invalid-feedback>
                            {{ validationContext.errors[0] }}
                          </b-form-invalid-feedback>
                        </b-form-group>
                      </validation-provider>
                    </b-col>
                    <b-col
                      md="4"
                      class="mb-1"
                    >
                      <b-button
                        v-ripple.400="'rgba(255, 255, 255, 0.15)'"
                        variant="primary"
                        @click="repeateAgain"
                        class="mt-2"
                        :disabled="addButtonDisabled"
                      >
                        <feather-icon
                          icon="PlusIcon"
                          class="mr-25"
                        />
                        <span>{{ $t('chat.chatModalNewTemplateMessageHandler.addButtonList') }}</span>
                      </b-button>
                    </b-col>
                  </b-row>
                  <!-- Row Loop -->
                  <div
                    v-for="(item, index) in templateButtons"
                    :id="index"
                    :key="index"
                    ref="row"
                  >
                    <h5
                      style="font-size: 12px"
                    >
                      {{ $t('chat.chatModalNewTemplateMessageHandler.buttonList') }} {{ index+1 }}
                    </h5>
                    <p
                      class="border-secondary rounded p-1"
                      style="width: 100%"
                    >
                      <b-button
                        v-ripple.400="'rgba(234, 84, 85, 0.15)'"
                        variant="danger"
                        class="btn-icon float-right"
                        @click="removeItem(index)"
                      >
                        <feather-icon
                          icon="TrashIcon"
                          size="16"
                        />
                      </b-button>
                      <b-row>
                        <!-- Item Name -->
                        <b-col md="6" style="padding-left: 0px !important">
                          <validation-provider
                            #default="validationContext"
                            :name="$t('chat.chatModalNewTemplateMessageHandler.label')"
                            rules="required|min:1"
                          >
                            <b-form-group
                              :label="$t('chat.chatModalNewTemplateMessageHandler.label')+'*'"
                              label-for="item-name"
                            >
                              <b-form-input
                                v-model="newQuickMessageLocal.buttonLabel[index]"
                                id="item-name"
                                type="text"
                                :state="getValidationState(validationContext)"
                                trim
                                :maxlength="25"
                                autocomplete="off"
                                :placeholder="$t('chat.chatModalNewTemplateMessageHandler.buttonPlaceholder')"
                              />
                              <b-form-invalid-feedback>
                                {{ validationContext.errors[0] }}
                              </b-form-invalid-feedback>
                            </b-form-group>
                          </validation-provider>
                        </b-col>
                      </b-row>
                      <b-row>
                        <!-- Item Name -->
                        <b-col md="10">
                          <validation-provider
                            #default="validationContext"
                            :name="$t('chat.chatModalNewTemplateMessageHandler.description')"
                            rules=""
                          >
                            <b-form-group
                              :label="$t('chat.chatModalNewTemplateMessageHandler.description')"
                              label-for="item-name"
                            >
                              <b-form-input
                                v-model="newQuickMessageLocal.buttonDescription[index]"
                                id="item-name"
                                type="text"
                                :state="getValidationState(validationContext)"
                                trim
                                :maxlength="40"
                                autocomplete="off"
                                :placeholder="$t('chat.chatModalNewTemplateMessageHandler.descriptionPlaceholder')"
                              />
                              <b-form-invalid-feedback>
                                {{ validationContext.errors[0] }}
                              </b-form-invalid-feedback>
                            </b-form-group>
                          </validation-provider>
                        </b-col>
                      </b-row>
                    </p>
                    <div>&nbsp;</div>
                  </div>
                </span>
              </b-form>
            </div>
          </span>
        </span>
        <span
          v-if="newQuickMessageLocal.typeFormatMessage && newQuickMessageLocal.typeFormatMessage.id == 2"
        >
          <!-- Texto do cabeçalho -->
          <validation-provider
            #default="validationContext"
            :name="$t('chat.chatModalNewTemplateMessageHandler.media')"
            rules="required"
          >
            <b-form-group
              :label="$t('chat.chatModalNewTemplateMessageHandler.media')"
              label-for="task-title"
            >
              <b-form-file
                v-model="fileSelected"
                ref="importFileTemplate"
                name="importFileTemplate"
                id="importFileTemplate"
                :state="getValidationState(validationContext)"
                accept=".mp3"
                :placeholder="$t('chat.chatModalNewTemplateMessageHandler.filePlaceholder')"
                :drop-placeholder="$t('chat.chatModalNewTemplateMessageHandler.filePlaceholder')"
                @change="handleFileUpload"
              />
              <b-form-invalid-feedback>
                {{ validationContext.errors[0] }}
              </b-form-invalid-feedback>
            </b-form-group>
          </validation-provider>

          <!-- Onde o tipo de mensagem rápida é Ligação via WhatsApp -->
          <span
            v-if="typeQuickMessageId == 5"
          >
            <b-row>
                <b-col
                  md="12"
                >
                  <!-- Nome do template -->
                  <validation-provider
                    #default="validationContext"
                    name="Name"
                    rules="required|min:2"
                  >
                    <b-form-group
                      :label="$t('chat.chatModalNewTemplateMessageHandler.name')"
                      label-for="task-title"
                    >
                      <b-form-input
                        id="task-title"
                        v-model="newQuickMessageLocal.qui_positives_responses"
                        autofocus
                        :state="getValidationState(validationContext)"
                        trim
                        :placeholder="$t('chat.chatModalNewTemplateMessageHandler.namePlaceholder')"
                        type="text"
                        :maxlength="512"
                        autocomplete="off"
                        @keyup="responsesValidate()"
                      />

                      <b-form-invalid-feedback>
                        {{ validationContext.errors[0] }}
                      </b-form-invalid-feedback>
                    </b-form-group>
                  </validation-provider> 
              </b-col>
            </b-row>
            <b-row>
                <b-col
                  md="12"
                >
                  <!-- Nome do template -->
                  <validation-provider
                    #default="validationContext"
                    name="Name"
                    rules="required|min:2"
                  >
                    <b-form-group
                      :label="$t('chat.chatModalNewTemplateMessageHandler.name')"
                      label-for="task-title"
                    >
                      <b-form-input
                        id="task-title"
                        v-model="newQuickMessageLocal.qui_negatives_responses"
                        autofocus
                        :state="getValidationState(validationContext)"
                        trim
                        :placeholder="$t('chat.chatModalNewTemplateMessageHandler.namePlaceholder')"
                        type="text"
                        :maxlength="512"
                        autocomplete="off"
                        @keyup="responsesValidate()"
                      />

                      <b-form-invalid-feedback>
                        {{ validationContext.errors[0] }}
                      </b-form-invalid-feedback>
                    </b-form-group>
                  </validation-provider> 
              </b-col>
            </b-row>
          </span>
        </span>

        <!-- Se for um ARQUIVO -->
        <span
          v-if="newQuickMessageLocal.typeFormatMessage && newQuickMessageLocal.typeFormatMessage.id == 3"
        >
          <validation-provider
            #default="validationContext"
            :name="$t('chat.chatModalNewTemplateMessageHandler.media')"
            rules="required"
          >
            <b-form-group
              :label="$t('chat.chatModalNewTemplateMessageHandler.media')"
              label-for="task-title"
            >
              <b-form-file
                v-model="fileSelected"
                ref="importFileTemplate"
                name="importFileTemplate"
                id="importFileTemplate"
                :state="getValidationState(validationContext)"
                accept=".pdf"
                :placeholder="$t('chat.chatModalNewTemplateMessageHandler.filePlaceholder')"
                :drop-placeholder="$t('chat.chatModalNewTemplateMessageHandler.filePlaceholder')"
                @change="handleFileUpload"
              />
              <b-form-invalid-feedback>
                {{ validationContext.errors[0] }}
              </b-form-invalid-feedback>
            </b-form-group>
          </validation-provider>
        </span>

        <!-- Se for um VÍDEO -->
        <span
          v-if="newQuickMessageLocal.typeFormatMessage && newQuickMessageLocal.typeFormatMessage.id == 4"
        >
          <validation-provider
            #default="validationContext"
            :name="$t('chat.chatModalNewTemplateMessageHandler.media')"
            rules="required"
          >
            <b-form-group
              :label="$t('chat.chatModalNewTemplateMessageHandler.media')"
              label-for="task-title"
            >
              <b-form-file
                v-model="fileSelected"
                ref="importFileTemplate"
                name="importFileTemplate"
                id="importFileTemplate"
                :state="getValidationState(validationContext)"
                accept=".mp4"
                :placeholder="$t('chat.chatModalNewTemplateMessageHandler.filePlaceholder')"
                :drop-placeholder="$t('chat.chatModalNewTemplateMessageHandler.filePlaceholder')"
                @change="handleFileUpload"
              />
              <b-form-invalid-feedback>
                {{ validationContext.errors[0] }}
              </b-form-invalid-feedback>
            </b-form-group>
          </validation-provider>
        </span>

        


        <!-- Form Actions -->
        <div class="d-flex mt-2 modal-footer">
          <b-button
            v-ripple.400="'rgba(255, 255, 255, 0.15)'"
            variant="primary"
            class="mr-2"
            type="submit"
          >
            <feather-icon
              icon="SaveIcon"
              class="mr-50"
            />
            <span class="align-middle">{{ $t('chat.chatModalNewQuickMessageHandler.save') }}</span>
          </b-button>
        </div>
      </b-form>
    </validation-observer>  
  </div>
</template>

<script>
import {
  BButton, BModal, VBModal, BForm, BFormInput, BFormGroup, BTable, BFormCheckbox, BCard, BRow, BCol, 
  BAvatar, BBadge, BFormInvalidFeedback, BListGroup, BListGroupItem, BFormFile,
} from 'bootstrap-vue'
import vSelect from 'vue-select'
import Ripple from 'vue-ripple-directive'
import axios from '@axios'
import { toRefs } from '@vue/composition-api'
import { ValidationProvider, ValidationObserver } from 'vee-validate'
import useChatModalNewQuickMessageHandler from './useChatModalNewQuickMessageHandler'
import { TwemojiPicker } from '@kevinfaguiar/vue-twemoji-picker'
import EmojiAllData from '@kevinfaguiar/vue-twemoji-picker/emoji-data/pt/emoji-all-groups.json'
import EmojiDataAnimalsNature from '@kevinfaguiar/vue-twemoji-picker/emoji-data/pt/emoji-group-animals-nature.json'
import EmojiDataFoodDrink from '@kevinfaguiar/vue-twemoji-picker/emoji-data/pt/emoji-group-food-drink.json'
import EmojiGroups from '@kevinfaguiar/vue-twemoji-picker/emoji-data/emoji-groups.json'
import formValidation from '@core/comp-functions/forms/form-validation'
import { quillEditor } from 'vue-quill-editor'
import flatPickr from 'vue-flatpickr-component'
import VuePhoneNumberInput from 'vue-phone-number-input'
import { heightTransition } from '@core/mixins/ui/transition'
import ToastificationContent from '@core/components/toastification/ToastificationContent.vue'
import Vue from 'vue'
import VueClipboard from 'vue-clipboard2'

// Faz com que seja possível copiar dentro de modals
VueClipboard.config.autoSetContainer = true 

Vue.use(VueClipboard)

export default {
  components: {
    BButton,
    BForm,
    BModal,
    VBModal,
    BFormInput,
    BFormGroup,
    vSelect,
    BTable,
    BFormCheckbox,
    BCard,
    BRow,
    BCol,
    BBadge,
    BAvatar,
    BFormInvalidFeedback,
    BListGroup,
    BListGroupItem,
    BFormFile,
    
    // Form Validation
    ValidationProvider,
    ValidationObserver,
    
    //Editor
    quillEditor,
    flatPickr,

    VuePhoneNumberInput,

    //Emojis
    'twemoji-picker': TwemojiPicker,
    EmojiGroups,
    EmojiDataFoodDrink,
    EmojiDataAnimalsNature,

    //Toast Notification
    ToastificationContent,
  },
  directives: {
    'b-modal': VBModal,
    Ripple,
  },
  mixins: [heightTransition],
  props: {
    newQuickMessage: {
      type: Object,
      required: true,
    },
    clearNewQuickMessageData: {
      type: Function,
      required: true,
    },
    typeQuickMessageId: {
      type: Number,
      required: true,
    },
  },
  computed: {
    emojiDataAll() {
      return EmojiAllData;
    },
    emojiGroups() {
      return EmojiGroups;
    }
  },
  data() {
    return {
      selectMode: 'single',
      templateButtons: [],
      selected: [],
      typeParameters: [],
      typeButtons: [],
      typeCallActions: [],
      fileSelected: '',
      addButtonDisabled: true,
      phoneNumber: '',
      typeFormatMessages: [],
      totalCharactersBody: 0,
    }
  },
  methods: {
    //Insere emojis
    emojiSelected: function(emoji) {
      const range = this.$refs.myEditor.quill.getSelection()
      this.$refs.myEditor.quill.insertText(range.index, emoji)
    },
    onCopy: function (e) {
      this.$toast({
        component: ToastificationContent,
        props: {
          title: 'Copied Tag!',
          icon: 'CheckIcon',
          variant: 'success',
        },
      })
    },
    onError: function (e) {
      this.$toast({
        component: ToastificationContent,
        props: {
          title: 'Failed to Copy!',
          icon: 'AlertTriangleIcon',
          variant: 'success',
        },
      })
    },
    setCallAction() {
      this.typeCallActionsAux = this.typeCallActions
      //Se o tipo de botão for CHAMADA PARA AÇÃO e o usuário tenha adicionado apenas um botão
      if(this.newQuickMessageLocal.typeButton.id == 2 && this.templateButtons.length == 1) {
        this.callActionDisabled = false
        if(this.newQuickMessageLocal.callActions[0] == null) {
          //Atribui por padrão o tipo URL de chamada para ação 
          this.newQuickMessageLocal.callActions[0] = this.typeCallActions[0]
        }
      } //Se o tipo de botão for CHAMADA PARA AÇÃO e o usuário tenha adicionado o segundo botão
      else if(this.newQuickMessageLocal.typeButton.id == 2 && this.templateButtons.length == 2) {
        //Desabilita o select
        this.callActionDisabled = true
        //Se o botão de chamada para ação for uma URL
        if(this.newQuickMessageLocal.callActions[0].id == 1) {
          //Atribui o tipo NÚMERO DE TELEFONE para o segundo botão
          this.newQuickMessageLocal.callActions[1] = this.typeCallActions[1]
        }
        else {
          //Atribui o tipo URL para o segundo botão
          this.newQuickMessageLocal.callActions[1] = this.typeCallActions[0]
        }
      }
    },
    checkTypeFormatSelected(typeFormatSelected) {
      //Se o formato selecionado foi TEXTO
      if(typeFormatSelected.id == 1) {
        console.log('this.typeButtons')
        console.log(this.typeButtons)
        this.typeButtons[2] = {created_at: null, id: 3, tem_name:'Lista - Somente para API Oficial', tem_status: 'A', update_at: null}
      }
    },
    clearValuesButton(index) {
      //Se a chamada para ação foi uma URL
      if(this.newQuickMessageLocal.callActions[index].id == 1) {
        this.newQuickMessageLocal.buttonUrl = ''
      }
      else {
        this.newQuickMessageLocal.buttonPhone = ''
      }
      this.newQuickMessageLocal.buttonLabel[index] = ''
    },
    clearHeaderData(typeHeader) {
      console.log('typeHeader')
      console.log(typeHeader)
      if(typeHeader) {
        //Se o tipo de cabeçalho selecionado foi TEXTO
        if(typeHeader.id == 1) {
          //Limpa o campo de mídia
          document.getElementById("importFileTemplate").value = null;
        }
        else {
          //Limpa o campo de texto
          this.newQuickMessageLocal.header = ''
        }
      }
      else {
        //Limpa o campo de texto
        this.newQuickMessageLocal.header = ''
      }
      
      if(typeHeader) {
        if(typeHeader.id == 2) {
          //Remove o último elemento (A opção "Modelo")
          this.typeButtons.pop()
          this.typeButtons.push({id: 2, tem_name: 'Chamada para Ação - Não é possível selecionar esse tipo de botão quando a mensagem contém imagem'})
        }
      }
      else {
        this.typeButtons.pop()
        this.typeButtons.push({id: 2, tem_name: 'Chamada para Ação'})
      }
      
      
    },
    handleFileUpload (event) {
      this.$emit('upload-file', event.target.files[0])
    },
    repeateAgain() {
      this.templateButtons.push({
        id: this.nextTodoId += this.nextTodoId,
      })

      this.$nextTick(() => {
        this.trAddHeight(this.$refs.row[0].offsetHeight)
      })

      this.checkLimitButtons()

      this.setCallAction()
    },
    checkLimitButtons() {
      if(this.newQuickMessageLocal.typeButton == '' || this.newQuickMessageLocal.typeButton == null) {
        this.addButtonDisabled = true
      }
      else {
        this.addButtonDisabled = false

        //Se o tipo de botão é mensagem rápida e já foram adicionados 3 botões
        if(this.newQuickMessageLocal.typeButton.id == 1 && this.templateButtons.length == 3) {
          //Não deixa adicionar mais botões
          this.addButtonDisabled = true
        }
        //Se for um botão do tipo chamada para ação e já foram adicionados 2 botões
        else if(this.newQuickMessageLocal.typeButton.id == 2 && this.templateButtons.length == 2) {
          //Não deixa adicionar mais botões
          this.addButtonDisabled = true
        }
        else {
          this.addButtonDisabled = false
        }
      }
    },
    removeItem(index) {
      this.templateButtons.splice(index, 1)
      this.trTrimHeight(this.$refs.row[0].offsetHeight)

      this.checkLimitButtons()
      this.setCallAction()
      this.clearValuesButton(index)

    },
    //Seta número completo
    setPhoneNumber: function(data) {
      this.phoneNumber = data.formattedNumber
    },
    initTrHeight() {
      this.trSetHeight(null)
      this.$nextTick(() => {
        this.trSetHeight(this.$refs.form.scrollHeight)
      })
    },
    onCopy: function (e) {
      this.$toast({
        component: ToastificationContent,
        props: {
          title: 'Copied Tag!',
          icon: 'CheckIcon',
          variant: 'success',
        },
      })
    },
    onError: function (e) {
      this.$toast({
        component: ToastificationContent,
        props: {
          title: 'Failed to Copy!',
          icon: 'AlertTriangleIcon',
          variant: 'success',
        },
      })
    },
    //Conta quantas e quais variáveis estão presentes no texto e limita a quantidade de caracteres no texto
    countVariablesText() {
      //Conta a quantidade variáveis ( {{}} ) no texto digitado
      this.templateVariables = this.newQuickMessageLocal.content.match(/{{\d+}}/g)
      
      //Se é uma mensagem de SMS
      if(this.typeQuickMessageId == 4) {
        //Define o limite total de caracteres em 160
        let limit = 160
        //Se o total de caracteres digitados for maior que o limite
        if (this.$refs.myEditor.quill.getLength() > limit) {
          //Deleta todos os caracteres excedentes ao limite
          this.$refs.myEditor.quill.deleteText(limit, this.$refs.myEditor.quill.getLength())
        }
      }

      this.totalCharactersBody = this.$refs.myEditor.quill.getLength()-1
    },
    responsesValidate () {
      //Converte as caracteres em minúsculo
      var variableAux = this.newQuickMessageLocal.qui_positives_responses.toLowerCase()
      this.newQuickMessageLocal.qui_positives_responses = variableAux
      //Substitiu os espaços por underscore
      variableAux = this.newQuickMessageLocal.qui_positives_responses.split(' ').join('_')
      this.newQuickMessageLocal.qui_positives_responses = variableAux

      variableAux = this.newQuickMessageLocal.qui_positives_responses.replace(/[^a-zA-Z0-9, ]/g, " ")
      this.newQuickMessageLocal.qui_positives_responses = variableAux
    },
  },
  mounted() {
    this.initTrHeight()
  },
  destroyed() {
    window.removeEventListener('resize', this.initTrHeight)
  },
  created() { 
    //Traz os tipos de botões
    axios
      .get('/api/chat/fetch-type-buttons/A')
      .then(response => {
        //console.log(response.data)
        this.typeButtons = response.data.typeButtons
      });

    //Traz os tipos de parâmetros que uma mensagem rápida pode ter
    axios
      .get('/api/chat/fetch-quick-messages-type-parameters/A')
      .then(response => {
        //console.log(response.data)
        this.typeParameters = response.data.typeParameters
      });

    //Traz os tipos de parâmetros que uma mensagem rápida pode ter
    axios
      .get('/api/chat/fetch-type-format-messages')
      .then(response => {
        //console.log(response.data)
        this.typeFormatMessages = response.data.typeFormatMessages

        //Se for uma mensagem para SMS
        if(this.typeQuickMessageId == 4) {
          //this.typeFormatMessages.pop()
          this.typeFormatMessages = []
          this.typeFormatMessages.push({id: 1, typ_description: 'Texto'})
          this.typeFormatMessages.push({id: 2, typ_description: 'Áudio - Disponível apenas para mensagens de WhatsApp'})
          this.typeFormatMessages.push({id: 3, typ_description: 'Arquivo - Disponível apenas para mensagens de WhatsApp'})
        }//Se for uma Ligação via WhatsApp
        else if(this.typeQuickMessageId == 5) {
          //this.typeFormatMessages.shift()
          this.typeFormatMessages = []
          this.typeFormatMessages.push({id: 1, typ_description: 'Texto - Disponível apenas para mensagens de WhatsApp e SMS'})
          this.typeFormatMessages.push({id: 2, typ_description: 'Áudio'})
          this.typeFormatMessages.push({id: 3, typ_description: 'Arquivo - Disponível apenas para mensagens de WhatsApp'})
        }
      });
    
    //Traz os tipos de chamadas para ação
    axios
      .get('/api/chat/fetch-type-call-actions/A')
      .then(response => {
        //console.log(response.data)
        this.typeCallActions = response.data.typeCallActions
      });

    window.addEventListener('resize', this.initTrHeight)
  },
  setup(props,{ emit }) {
    /*
     ? This is handled quite differently in SFC due to deadlock of `useFormValidation` and this composition function.
     ? If we don't handle it the way it is being handled then either of two composition function used by this SFC get undefined as one of it's argument.
     * The Trick:

     * We created reactive property `clearFormData` and set to null so we can get `resetEventLocal` from `useCalendarEventHandler` composition function.
     * Once we get `resetEventLocal` function which is required by `useFormValidation` we will pass it to `useFormValidation` and in return we will get `clearForm` function which shall be original value of `clearFormData`.
     * Later we just assign `clearForm` to `clearFormData` and can resolve the deadlock. 😎

     ? Behind The Scene
     ? When we passed it to `useCalendarEventHandler` for first time it will be null but right after it we are getting correct value (which is `clearForm`) and assigning that correct value.
     ? As `clearFormData` is reactive it is being changed from `null` to corrent value and thanks to reactivity it is also update in `useCalendarEventHandler` composition function and it is getting correct value in second time and can work w/o any issues.
    */

    const {
      newQuickMessageLocal,
      resetTransferLocal,
      // UI
      onSubmit,
    } = useChatModalNewQuickMessageHandler(toRefs(props), emit)

    
    const {
      refFormObserver,
      getValidationState,
      resetForm,
      clearForm,
    } = formValidation(resetTransferLocal, props.clearActionData)

    const editorOption = {
      modules: {
        toolbar: '#quill-toolbar',
      },
      placeholder: 'Write your content',
    }


    return {
      // Add New Event
      newQuickMessageLocal,
      resetTransferLocal,
      onSubmit,

      //Quill Editor
      editorOption,
      
      
      refFormObserver,
      getValidationState,
      resetForm,
      clearForm,
    }
  },
}
</script>
<style lang="scss" scoped>
@import '~@core/scss/base/bootstrap-extended/include';

.assignee-selector {
  ::v-deep .vs__dropdown-toggle {
  padding-left: 0;
  }
}

#quil-content ::v-deep {
  > .ql-container {
    border-bottom: 0;
  }

  + #quill-toolbar {
    border-top-left-radius: 0;
    border-top-right-radius: 0;
    border-bottom-left-radius: $border-radius;
    border-bottom-right-radius: $border-radius;
  }
}

.pointer-cursor {
  cursor: pointer;
}
</style>
<style lang="scss">
@import '@core/scss/vue/libs/vue-select.scss';
@import '@core/scss/vue/libs/vue-flatpicker.scss';
@import '@core/scss/vue/libs/quill.scss';
</style>