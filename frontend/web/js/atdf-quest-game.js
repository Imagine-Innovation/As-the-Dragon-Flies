import OpenAI from 'openai';
import { playAudio } from 'openai/helpers/audio';
class VirtualTableTop {
    constructor(options = {}) {
        this.context = {};
        this.modal = null;
        this.options = {
            ...options
        };
    }

    init() {
        this.context = {
            storyId: $('#hiddenStoryId').html(),
            questId: $('#hiddenQuestId').html(),
            playerId: $('#hiddenPlayerId').html(),
            currentPlayerId: $('#hiddenCurrentPlayerId').html(),
            missionId: $('#hiddenQuestMissionId').html(),
            questProgressId: $('#hiddenQuestProgressId').html(),
            actionId: $('#hiddenQuestActionId').html()
        };
    }

    static refresh(questId, sessionId, message = null) {
        Logger.log(1, 'refresh', `questId=${questId}, sessionId=${sessionId}, message=${message}`);
        if (message)
            ToastManager.show('Tavern message', message, 'info');
        VirtualTableTop._updateQuestMembers(questId);
    }

    static _updateQuestMembers(questId) {
        Logger.log(2, '_updateQuestMembers', `questId=${questId}`);
        const asideTarget = `#questMembers`;
        if (!DOMUtils.exists(asideTarget))
            return;
        const offcanvasTarget = `#offcanvasQuestMembers`;
        if (!DOMUtils.exists(offcanvasTarget))
            return;
        AjaxUtils.request({
            url: 'quest/ajax-quest-members',
            method: 'GET',
            data: {questId: questId, render: 'game-members'},
            successCallback: (response) => {
                if (!response.error) {
                    const content = response.content;
                    $(asideTarget).html(content);
                    $(offcanvasTarget).html(content);
                }
            }
        });
    }

    missionDescription(questId) {
        Logger.log(1, 'missionDescription', `questId=${questId}`);
        const target = `#missionDescription`;
        if (!DOMUtils.exists(target))
            return;
        AjaxUtils.request({
            url: 'game/ajax-mission',
            method: 'GET',
            data: this.context,
            successCallback: (response) => {
                if (!response.error) {
                    const content = response.content;
                    $(target).html(content);
                }
            }
        });
    }

    actions(questId) {
        Logger.log(2, 'actions', `questId=${questId}`);
        const target = `#actionList`;
        if (!DOMUtils.exists(target))
            return;
        AjaxUtils.request({
            url: 'game/ajax-actions',
            method: 'GET',
            data: {questId: questId},
            successCallback: (response) => {
                let content = `???`;
                if (!response.error) {
                    content = response.content;
                } else {
                    content = response.msg;
                }
                $(target).html(content);
            }
        });
    }

    _showModal(modalId) {
        Logger.log(2, '_showModal', `modalId=${modalId}`);
        const modalElement = document.querySelector(modalId);
        this.modal = new bootstrap.Modal(modalElement);
        this.modal.show();
    }

    _hideModal() {
        Logger.log(2, '_hideModal', ``);
        if (this.modal) {
            this.modal.hide();
            this.modal = null;
        }
    }

    talk(actionId, replyId) {
        Logger.log(1, 'talk', `actionId=${actionId}, replyId=${replyId}`);
        const target = `#actionFeedback`;
        $(target).html(`Talk: actionId=${actionId}, replyId=${replyId}`);
        this._showModal('#npcDialogModal');
        // Store the current action in the context
        this.context.actionId = actionId;
        this._dialog(replyId);
    }

    reply(replyId) {
        Logger.log(1, 'reply', `replyId=${replyId}`);
        const target = `#actionFeedback`;
        $(target).html(`Reply: replyId=${replyId}`);
        this._dialog(replyId);
    }

    _dialog(replyId) {
        Logger.log(2, '_dialog', `replyId=${replyId}`);
        const target = `#currentDialog`;
        if (!DOMUtils.exists(target))
            return;
        AjaxUtils.request({
            url: 'game/ajax-dialog',
            method: 'GET',
            data: {
                ...this.context,
                replyId: replyId
            },
            successCallback: (response) => {
                if (!response.error) {
                    $(target).html(response.content);
                    if (response.text) {
                        //this.__speakText(response.text);
                        this.__TTS(response.text);
                    }
                }
            }
        });
    }

    __TTS(input) {
        const openai = new OpenAI();
        const instructions = "Voice Affect: Calm, composed, and reassuring. Competent and in control, instilling trust.\n\nTone: Sincere, empathetic, with genuine concern for the customer and understanding of the situation.\n\nPacing: Slower during the apology to allow for clarity and processing. Faster when offering solutions to signal action and resolution.\n\nEmotions: Calm reassurance, empathy, and gratitude.\n\nPronunciation: Clear, precise: Ensures clarity, especially with key details. Focus on key words like \"refund\" and \"patience.\" \n\nPauses: Before and after the apology to give space for processing the apology.";
        const response = await openai.audio.speech.create({
            model: 'gpt-4o-mini-tts',
            voice: 'verse',
            input,
            instructions
        });
        await playAudio(response);
    }

// Fonction principale pour déclencher la synthèse vocale
    __speakText(textToRead) {
        Logger.log(3, '__speakText', `textToRead=${textToRead}`);
        // 1. Vérifier la compatibilité du navigateur
        if ('speechSynthesis' in window) {
// 2. Créer une nouvelle instance de SpeechSynthesisUtterance
            const utterance = new SpeechSynthesisUtterance(textToRead);
            // --- Paramètres optionnels (personnalisation de la voix) ---

            // Langue (par exemple, 'fr-FR' pour le français)
            // Les voix disponibles dépendent du système d'exploitation et du navigateur de l'utilisateur.
            utterance.lang = 'fr-FR';
            // Vitesse de lecture (1 est la vitesse normale)
            // utterance.rate = 1.0;

            // Tonalité/hauteur (1 est la hauteur normale)
            // utterance.pitch = 1.0;

            // Sélectionner une voix spécifique (optionnel et plus complexe)
            /*
             const voices = speechSynthesis.getVoices();
             // Exemple : sélectionner la première voix française trouvée
             const frenchVoice = voices.find(voice => voice.lang === 'fr-FR');
             if (frenchVoice) {
             utterance.voice = frenchVoice;
             }
             */

            // 3. Lire le texte
            window.speechSynthesis.speak(utterance);
        } else {
// Afficher un message si l'API n'est pas supportée
            document.getElementById('support-message').textContent =
                    "Désolé, votre navigateur ne supporte pas l'API Web Speech Synthèse Vocale.";
            console.error("API Web Speech non supportée.");
        }
    }

    makeAction(actionId) {
        Logger.log(1, 'makeAction', `actionId=${actionId}`);
        const target = `#actionFeedback`;
        $(target).html(`Action: actionId=${actionId}`);
    }

    evaluateAction() {
        Logger.log(1, 'evaluateAction', ``);
        AjaxUtils.request({
            url: 'game/ajax-evaluate',
            method: 'POST',
            data: this.context,
            successCallback: (response) => {
                if (!response.error) {
                    this._hideModal();
                }
            }
        });
    }
}
