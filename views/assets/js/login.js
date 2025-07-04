/**
 * CRM Ligeros - Sistema de Login Interactivo
 * login.js - Manejo completo del formulario de login
 */

(function () {
	"use strict";

	// ========================================================================
	// CONFIGURACIÓN Y CONSTANTES
	// ========================================================================

	const CONFIG = {
		// Configuración por defecto
		maxAttempts: 5,
		lockoutTime: 900, // 15 minutos
		alertTimeout: 10000, // 10 segundos
		animationDuration: 300,

		// Selectores
		selectors: {
			form: ".login-form",
			loginButton: ".btn-login",
			identifierField: "#identifier",
			passwordField: "#password",
			rememberField: "#remember_me",
			togglePassword: ".toggle-password",
			alerts: ".alert",
			loadingSpinner: ".btn-loading",
			buttonContent: ".btn-content",
		},

		// Clases CSS
		classes: {
			loading: "loading",
			invalid: "invalid",
			valid: "valid",
			hidden: "hidden",
			shake: "shake",
			pulse: "pulse",
		},
	};

	// ========================================================================
	// UTILIDADES
	// ========================================================================

	/**
	 * Utilidades generales
	 */
	const Utils = {
		/**
		 * Selector seguro de elementos
		 */
		$(selector, context = document) {
			return context.querySelector(selector);
		},

		/**
		 * Selector múltiple
		 */
		$$(selector, context = document) {
			return Array.from(context.querySelectorAll(selector));
		},

		/**
		 * Debounce para optimizar eventos
		 */
		debounce(func, wait) {
			let timeout;
			return function executedFunction(...args) {
				const later = () => {
					clearTimeout(timeout);
					func(...args);
				};
				clearTimeout(timeout);
				timeout = setTimeout(later, wait);
			};
		},

		/**
		 * Sanitizar input del usuario
		 */
		sanitizeInput(str) {
			const div = document.createElement("div");
			div.textContent = str;
			return div.innerHTML;
		},

		/**
		 * Validar email básico
		 */
		isValidEmail(email) {
			const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
			return emailRegex.test(email);
		},

		/**
		 * Mostrar/ocultar elemento con animación
		 */
		toggleElement(element, show, animation = "fade") {
			if (!element) return;

			if (show) {
				element.style.display = "";
				element.style.opacity = "0";
				element.style.transform =
					animation === "slide" ? "translateY(-10px)" : "scale(0.95)";

				requestAnimationFrame(() => {
					element.style.transition = `all ${CONFIG.animationDuration}ms ease`;
					element.style.opacity = "1";
					element.style.transform = "translateY(0) scale(1)";
				});
			} else {
				element.style.transition = `all ${CONFIG.animationDuration}ms ease`;
				element.style.opacity = "0";
				element.style.transform =
					animation === "slide" ? "translateY(-10px)" : "scale(0.95)";

				setTimeout(() => {
					element.style.display = "none";
				}, CONFIG.animationDuration);
			}
		},

		/**
		 * Crear elemento con contenido
		 */
		createElement(tag, options = {}) {
			const element = document.createElement(tag);

			Object.entries(options).forEach(([key, value]) => {
				if (key === "text") {
					element.textContent = value;
				} else if (key === "html") {
					element.innerHTML = value;
				} else if (key === "classes") {
					element.className = Array.isArray(value)
						? value.join(" ")
						: value;
				} else if (key === "attributes") {
					Object.entries(value).forEach(([attr, val]) => {
						element.setAttribute(attr, val);
					});
				} else {
					element[key] = value;
				}
			});

			return element;
		},
	};

	// ========================================================================
	// GESTIÓN DE ALERTAS
	// ========================================================================

	const AlertManager = {
		/**
		 * Crear una alerta
		 */
		create(type, message, options = {}) {
			const {
				icon = this.getIcon(type),
				dismissible = true,
				timeout = CONFIG.alertTimeout,
				animate = true,
			} = options;

			const alert = Utils.createElement("div", {
				classes: ["alert", `alert-${type}`],
				attributes: { role: "alert" },
			});

			const alertIcon = Utils.createElement("div", {
				classes: ["alert-icon"],
				text: icon,
			});

			const alertContent = Utils.createElement("div", {
				classes: ["alert-content"],
				html: message,
			});

			alert.appendChild(alertIcon);
			alert.appendChild(alertContent);

			// Botón de cerrar si es dismissible
			if (dismissible) {
				const closeBtn = Utils.createElement("button", {
					classes: ["alert-close"],
					text: "×",
					attributes: {
						type: "button",
						"aria-label": "Cerrar alerta",
					},
				});

				closeBtn.addEventListener("click", () => this.remove(alert));
				alert.appendChild(closeBtn);
			}

			// Auto-dismiss con timeout
			if (timeout > 0) {
				setTimeout(() => this.remove(alert), timeout);
			}

			return alert;
		},

		/**
		 * Mostrar alerta en el contenedor
		 */
		show(type, message, options = {}) {
			const container =
				Utils.$(".alerts-container") || this.createContainer();
			const alert = this.create(type, message, options);

			container.appendChild(alert);

			// Animación de entrada
			requestAnimationFrame(() => {
				alert.style.opacity = "0";
				alert.style.transform = "translateY(-20px)";
				alert.style.transition = `all ${CONFIG.animationDuration}ms ease`;

				requestAnimationFrame(() => {
					alert.style.opacity = "1";
					alert.style.transform = "translateY(0)";
				});
			});

			return alert;
		},

		/**
		 * Remover alerta con animación
		 */
		remove(alert) {
			if (!alert || !alert.parentNode) return;

			alert.style.transition = `all ${CONFIG.animationDuration}ms ease`;
			alert.style.opacity = "0";
			alert.style.transform = "translateY(-20px)";
			alert.style.height = alert.offsetHeight + "px";

			setTimeout(() => {
				alert.style.height = "0";
				alert.style.marginBottom = "0";
				alert.style.paddingTop = "0";
				alert.style.paddingBottom = "0";
			}, CONFIG.animationDuration / 2);

			setTimeout(() => {
				if (alert.parentNode) {
					alert.parentNode.removeChild(alert);
				}
			}, CONFIG.animationDuration);
		},

		/**
		 * Limpiar todas las alertas
		 */
		clearAll() {
			const alerts = Utils.$$(".alert");
			alerts.forEach((alert) => this.remove(alert));
		},

		/**
		 * Crear contenedor de alertas si no existe
		 */
		createContainer() {
			let container = Utils.$(".alerts-container");
			if (!container) {
				container = Utils.createElement("div", {
					classes: ["alerts-container"],
				});

				const form = Utils.$(CONFIG.selectors.form);
				if (form) {
					form.parentNode.insertBefore(container, form);
				}
			}
			return container;
		},

		/**
		 * Obtener icono por tipo
		 */
		getIcon(type) {
			const icons = {
				error: "⚠️",
				success: "✅",
				warning: "⏰",
				info: "ℹ️",
			};
			return icons[type] || "📝";
		},
	};

	// ========================================================================
	// VALIDACIÓN DE FORMULARIO
	// ========================================================================

	const FormValidator = {
		/**
		 * Reglas de validación
		 */
		rules: {
			identifier: {
				required: true,
				minLength: 3,
				maxLength: 100,
				message: "El usuario debe tener entre 3 y 100 caracteres",
			},
			password: {
				required: true,
				minLength: 8,
				maxLength: 255,
				message: "La contraseña debe tener al menos 8 caracteres",
			},
		},

		/**
		 * Validar un campo específico
		 */
		validateField(field, value, rule) {
			const errors = [];

			if (rule.required && (!value || value.trim() === "")) {
				errors.push("Este campo es obligatorio");
			}

			if (value && rule.minLength && value.length < rule.minLength) {
				errors.push(`Mínimo ${rule.minLength} caracteres`);
			}

			if (value && rule.maxLength && value.length > rule.maxLength) {
				errors.push(`Máximo ${rule.maxLength} caracteres`);
			}

			// Validación específica para identifier (email o username)
			if (field === "identifier" && value && value.includes("@")) {
				if (!Utils.isValidEmail(value)) {
					errors.push("El email no tiene un formato válido");
				}
			}

			return errors;
		},

		/**
		 * Validar todo el formulario
		 */
		validateForm(formData) {
			const errors = {};
			let isValid = true;

			Object.entries(this.rules).forEach(([field, rule]) => {
				const value = formData.get(field);
				const fieldErrors = this.validateField(field, value, rule);

				if (fieldErrors.length > 0) {
					errors[field] = fieldErrors;
					isValid = false;
				}
			});

			return { isValid, errors };
		},

		/**
		 * Mostrar errores en el campo
		 */
		showFieldError(fieldName, errors) {
			const field = Utils.$(`#${fieldName}`);
			if (!field) return;

			// Remover errores previos
			this.clearFieldError(fieldName);

			// Añadir clase de error
			field.classList.add("error");

			// Crear mensaje de error
			const errorElement = Utils.createElement("div", {
				classes: ["field-error"],
				text: errors[0], // Mostrar solo el primer error
				attributes: {
					role: "alert",
					"aria-live": "polite",
				},
			});

			field.parentNode.appendChild(errorElement);
		},

		/**
		 * Limpiar errores del campo
		 */
		clearFieldError(fieldName) {
			const field = Utils.$(`#${fieldName}`);
			if (!field) return;

			field.classList.remove("error");
			const errorElement = field.parentNode.querySelector(".field-error");
			if (errorElement) {
				errorElement.remove();
			}
		},

		/**
		 * Limpiar todos los errores
		 */
		clearAllErrors() {
			Object.keys(this.rules).forEach((field) => {
				this.clearFieldError(field);
			});
		},
	};

	// ========================================================================
	// GESTIÓN DE ESTADO DE CARGA
	// ========================================================================

	const LoadingManager = {
		/**
		 * Mostrar estado de carga
		 */
		show(button) {
			if (!button) return;

			button.disabled = true;
			button.classList.add(CONFIG.classes.loading);

			const content = button.querySelector(
				CONFIG.selectors.buttonContent
			);
			const loading = button.querySelector(
				CONFIG.selectors.loadingSpinner
			);

			if (content) Utils.toggleElement(content, false);
			if (loading) Utils.toggleElement(loading, true);
		},

		/**
		 * Ocultar estado de carga
		 */
		hide(button) {
			if (!button) return;

			button.disabled = false;
			button.classList.remove(CONFIG.classes.loading);

			const content = button.querySelector(
				CONFIG.selectors.buttonContent
			);
			const loading = button.querySelector(
				CONFIG.selectors.loadingSpinner
			);

			if (loading) Utils.toggleElement(loading, false);
			if (content) Utils.toggleElement(content, true);
		},
	};

	// ========================================================================
	// GESTIÓN DE ALMACENAMIENTO LOCAL
	// ========================================================================

	const StorageManager = {
		/**
		 * Guardar datos del usuario (sin información sensible)
		 */
		saveUserPreferences(data) {
			try {
				const prefs = {
					rememberMe: data.rememberMe || false,
					lastUsername:
						data.identifier && !data.identifier.includes("@")
							? data.identifier
							: "",
					theme: data.theme || "auto",
				};
				localStorage.setItem("crm_user_prefs", JSON.stringify(prefs));
			} catch (error) {
				console.warn("No se pudieron guardar las preferencias:", error);
			}
		},

		/**
		 * Cargar preferencias del usuario
		 */
		loadUserPreferences() {
			try {
				const prefs = localStorage.getItem("crm_user_prefs");
				return prefs ? JSON.parse(prefs) : {};
			} catch (error) {
				console.warn("No se pudieron cargar las preferencias:", error);
				return {};
			}
		},

		/**
		 * Limpiar datos sensibles
		 */
		clearSensitiveData() {
			// Solo limpiar datos sensibles, mantener preferencias
			sessionStorage.clear();
		},
	};

	// ========================================================================
	// GESTOR PRINCIPAL DEL LOGIN
	// ========================================================================

	const LoginManager = {
		/**
		 * Inicializar el sistema de login
		 */
		init(form, config = {}) {
			if (!form) {
				console.error("Formulario de login no encontrado");
				return;
			}

			// Combinar configuración
			Object.assign(CONFIG, config);

			this.form = form;
			this.setupElements();
			this.bindEvents();
			this.loadUserPreferences();
			this.setupAccessibility();

			console.log("Sistema de login inicializado correctamente");
		},

		/**
		 * Configurar elementos del DOM
		 */
		setupElements() {
			this.elements = {
				form: this.form,
				loginButton: Utils.$(CONFIG.selectors.loginButton, this.form),
				identifierField: Utils.$(
					CONFIG.selectors.identifierField,
					this.form
				),
				passwordField: Utils.$(
					CONFIG.selectors.passwordField,
					this.form
				),
				rememberField: Utils.$(
					CONFIG.selectors.rememberField,
					this.form
				),
				toggleButtons: Utils.$$(
					CONFIG.selectors.togglePassword,
					this.form
				),
			};

			// Verificar elementos críticos
			if (
				!this.elements.loginButton ||
				!this.elements.identifierField ||
				!this.elements.passwordField
			) {
				console.error(
					"Elementos críticos del formulario no encontrados"
				);
				return false;
			}

			return true;
		},

		/**
		 * Configurar eventos
		 */
		bindEvents() {
			// Evento de envío del formulario
			this.form.addEventListener("submit", (e) => this.handleSubmit(e));

			// Validación en tiempo real
			this.elements.identifierField.addEventListener(
				"input",
				Utils.debounce(
					(e) =>
						this.validateFieldRealTime(
							"identifier",
							e.target.value
						),
					500
				)
			);

			this.elements.passwordField.addEventListener(
				"input",
				Utils.debounce(
					(e) =>
						this.validateFieldRealTime("password", e.target.value),
					500
				)
			);

			// Toggle de contraseña
			this.elements.toggleButtons.forEach((button) => {
				button.addEventListener("click", (e) => this.togglePassword(e));
			});

			// Limpieza al salir
			window.addEventListener("beforeunload", () => {
				this.cleanup();
			});

			// Manejo de teclas especiales
			this.form.addEventListener("keydown", (e) => this.handleKeydown(e));

			// Auto-dismiss de alertas existentes
			this.setupAlertAutoDismiss();
		},

		/**
		 * Manejar envío del formulario
		 */
		async handleSubmit(event) {
			event.preventDefault();

			// Limpiar errores previos
			FormValidator.clearAllErrors();
			AlertManager.clearAll();

			// Obtener datos del formulario
			const formData = new FormData(this.form);
			const data = {
				identifier: formData.get("identifier")?.trim(),
				password: formData.get("password"),
				rememberMe: formData.get("remember_me") === "on",
				csrf_token: formData.get("csrf_token"),
			};

			// Validar formulario
			const validation = FormValidator.validateForm(formData);
			if (!validation.isValid) {
				this.showValidationErrors(validation.errors);
				return;
			}

			// Mostrar estado de carga
			LoadingManager.show(this.elements.loginButton);

			try {
				// Simular delay mínimo para UX
				await new Promise((resolve) => setTimeout(resolve, 800));

				// El formulario se enviará normalmente al servidor
				// Guardar preferencias antes del envío
				StorageManager.saveUserPreferences(data);

				// El navegador procesará la respuesta del servidor
				this.form.submit();
			} catch (error) {
				console.error("Error durante el login:", error);
				AlertManager.show(
					"error",
					"Error inesperado. Por favor, inténtalo de nuevo."
				);
				LoadingManager.hide(this.elements.loginButton);
			}
		},

		/**
		 * Validación en tiempo real
		 */
		validateFieldRealTime(fieldName, value) {
			const rule = FormValidator.rules[fieldName];
			if (!rule) return;

			const errors = FormValidator.validateField(fieldName, value, rule);

			if (errors.length > 0) {
				FormValidator.showFieldError(fieldName, errors);
			} else {
				FormValidator.clearFieldError(fieldName);
			}
		},

		/**
		 * Mostrar errores de validación
		 */
		showValidationErrors(errors) {
			Object.entries(errors).forEach(([field, fieldErrors]) => {
				FormValidator.showFieldError(field, fieldErrors);
			});

			// Mostrar alerta general
			AlertManager.show(
				"error",
				"Por favor, corrige los errores en el formulario."
			);

			// Enfocar el primer campo con error
			const firstErrorField = Object.keys(errors)[0];
			const field = Utils.$(`#${firstErrorField}`);
			if (field) {
				field.focus();
				this.shakeElement(field);
			}
		},

		/**
		 * Toggle mostrar/ocultar contraseña
		 */
		togglePassword(event) {
			event.preventDefault();

			const button = event.currentTarget;
			const targetId = button.getAttribute("data-target");
			const passwordField = Utils.$(`#${targetId}`);

			if (!passwordField) return;

			const showText = button.querySelector(".show-text");
			const hideText = button.querySelector(".hide-text");

			if (passwordField.type === "password") {
				passwordField.type = "text";
				Utils.toggleElement(showText, false);
				Utils.toggleElement(hideText, true);
				button.setAttribute("aria-label", "Ocultar contraseña");
			} else {
				passwordField.type = "password";
				Utils.toggleElement(hideText, false);
				Utils.toggleElement(showText, true);
				button.setAttribute("aria-label", "Mostrar contraseña");
			}
		},

		/**
		 * Cargar preferencias del usuario
		 */
		loadUserPreferences() {
			const prefs = StorageManager.loadUserPreferences();

			// Restaurar último username si no es email
			if (prefs.lastUsername && !this.elements.identifierField.value) {
				this.elements.identifierField.value = prefs.lastUsername;
			}

			// Restaurar estado de "recordarme"
			if (prefs.rememberMe && this.elements.rememberField) {
				this.elements.rememberField.checked = true;
			}
		},

		/**
		 * Configurar accesibilidad
		 */
		setupAccessibility() {
			// Mejorar etiquetas ARIA
			this.elements.identifierField.setAttribute(
				"aria-describedby",
				"identifier-help"
			);
			this.elements.passwordField.setAttribute(
				"aria-describedby",
				"password-help"
			);

			// Configurar anuncios para lectores de pantalla
			const announcer = Utils.createElement("div", {
				attributes: {
					"aria-live": "polite",
					"aria-atomic": "true",
					class: "sr-only",
				},
			});
			document.body.appendChild(announcer);
			this.announcer = announcer;
		},

		/**
		 * Configurar auto-dismiss de alertas
		 */
		setupAlertAutoDismiss() {
			const existingAlerts = Utils.$$(".alert-success, .alert-warning");
			existingAlerts.forEach((alert) => {
				setTimeout(
					() => AlertManager.remove(alert),
					CONFIG.alertTimeout
				);
			});
		},

		/**
		 * Manejar teclas especiales
		 */
		handleKeydown(event) {
			// Enter en cualquier campo envía el formulario
			if (event.key === "Enter" && !event.shiftKey) {
				const target = event.target;
				if (target.tagName === "INPUT" && target.type !== "submit") {
					event.preventDefault();
					this.elements.loginButton.click();
				}
			}

			// Escape limpia errores
			if (event.key === "Escape") {
				FormValidator.clearAllErrors();
				AlertManager.clearAll();
			}
		},

		/**
		 * Efecto shake para errores
		 */
		shakeElement(element) {
			element.classList.add(CONFIG.classes.shake);
			setTimeout(() => {
				element.classList.remove(CONFIG.classes.shake);
			}, CONFIG.animationDuration);
		},

		/**
		 * Limpieza al salir
		 */
		cleanup() {
			// Limpiar campos sensibles
			if (this.elements.passwordField) {
				this.elements.passwordField.value = "";
			}

			// Limpiar datos sensibles del almacenamiento
			StorageManager.clearSensitiveData();
		},
	};

	// ========================================================================
	// INICIALIZACIÓN Y EXPOSICIÓN GLOBAL
	// ========================================================================

	// Exponer LoginManager globalmente
	window.LoginManager = LoginManager;
	window.CRMLogin = {
		LoginManager,
		AlertManager,
		FormValidator,
		LoadingManager,
		StorageManager,
		Utils,
	};

	// Auto-inicialización si se encuentra el formulario
	document.addEventListener("DOMContentLoaded", () => {
		const loginForm = Utils.$(CONFIG.selectors.form);
		if (loginForm) {
			// Esperar a que se cargue la configuración global
			setTimeout(() => {
				const config = window.CRMConfig || {};
				LoginManager.init(loginForm, config);
			}, 100);
		}
	});

	console.log("Sistema CRM Login JavaScript cargado correctamente");
})();
