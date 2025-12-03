document.addEventListener('DOMContentLoaded', function() {
    // Elementos del DOM
    const updateForm = document.getElementById('updateForm');
    const successAlert = document.getElementById('successAlert');
    const errorAlert = document.getElementById('errorAlert');
    const profilePictureInput = document.getElementById('profilePicture');
    const currentProfilePic = document.getElementById('currentProfilePic');
    const removePictureBtn = document.getElementById('removePicture');
    const newPasswordInput = document.getElementById('newPassword');
    const strengthBar = document.getElementById('strengthBar');
    const strengthLabel = document.getElementById('strengthLabel');
    const cancelBtn = document.getElementById('cancelBtn');
    
    // Cargar datos del usuario
    function loadUserData() {
        // Simulación de datos - en producción sería una llamada AJAX
        const userData = {
            nombres: "María José",
            apellidos: "García López",
            documento: "12345678A",
            email: "maria@example.com",
            telefono: "5551234567",
            fotoPerfil: "placeholder-profile.jpg"
        };
        
        // Llenar formulario
        document.getElementById('nombres').value = userData.nombres;
        document.getElementById('apellidos').value = userData.apellidos;
        document.getElementById('documento').value = userData.documento;
        document.getElementById('email').value = userData.email;
        document.getElementById('telefono').value = userData.telefono;
        currentProfilePic.src = userData.fotoPerfil;
    }
    
    // Manejar cambio de foto de perfil
    profilePictureInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file && file.type.match('image.*')) {
            const reader = new FileReader();
            reader.onload = function(event) {
                currentProfilePic.src = event.target.result;
            };
            reader.readAsDataURL(file);
        } else {
            showError('Por favor selecciona una imagen válida (JPEG, PNG)');
            this.value = '';
        }
    });
    
    // Eliminar foto de perfil
    removePictureBtn.addEventListener('click', function() {
        currentProfilePic.src = 'default-profile.jpg';
        profilePictureInput.value = '';
    });
    
    // Validar fortaleza de contraseña
    newPasswordInput.addEventListener('input', function() {
        const password = this.value;
        let strength = 0;
        
        // Reglas de validación
        if (password.length >= 8) strength += 1;
        if (/\d/.test(password)) strength += 1;
        if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength += 1;
        if (/[^a-zA-Z0-9]/.test(password)) strength += 1;
        
        // Actualizar UI
        const width = (strength / 4) * 100;
        strengthBar.style.width = width + '%';
        
        // Establecer color y texto según fortaleza
        const levels = [
            { color: '#dc3545', text: 'Débil' },
            { color: '#ffc107', text: 'Moderada' },
            { color: '#28a745', text: 'Fuerte' },
            { color: '#0a8048', text: 'Excelente' }
        ];
        
        const level = Math.min(strength, 3);
        strengthBar.style.backgroundColor = levels[level].color;
        strengthLabel.textContent = `Seguridad: ${levels[level].text}`;
        strengthLabel.style.color = levels[level].color;
    });
    
    // Mostrar mensaje de error
    function showError(message) {
        errorAlert.querySelector('p').textContent = message;
        errorAlert.style.display = 'flex';
        setTimeout(() => {
            errorAlert.style.display = 'none';
        }, 5000);
    }
    
    // Mostrar mensaje de éxito
    function showSuccess(message) {
        successAlert.querySelector('p').textContent = message;
        successAlert.style.display = 'flex';
        setTimeout(() => {
            successAlert.style.display = 'none';
        }, 5000);
    }
    
    // Validar formulario
    function validateForm() {
        let isValid = true;
        
        // Validar campos requeridos
        const requiredFields = [
            { id: 'nombres', errorId: 'nombresError', message: 'Por favor ingresa tus nombres' },
            { id: 'apellidos', errorId: 'apellidosError', message: 'Por favor ingresa tus apellidos' },
            { id: 'documento', errorId: 'documentoError', message: 'Por favor ingresa tu documento' },
            { id: 'email', errorId: 'emailError', message: 'Por favor ingresa un correo válido' }
        ];
        
        requiredFields.forEach(field => {
            const element = document.getElementById(field.id);
            const errorElement = document.getElementById(field.errorId);
            
            if (element.value.trim() === '') {
                errorElement.textContent = field.message;
                errorElement.style.display = 'block';
                isValid = false;
            } else {
                errorElement.style.display = 'none';
            }
        });
        
        // Validar formato de email
        const email = document.getElementById('email').value.trim();
        if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
            document.getElementById('emailError').style.display = 'block';
            isValid = false;
        }
        
        // Validar contraseña si se está cambiando
        const currentPassword = document.getElementById('currentPassword').value;
        const newPassword = document.getElementById('newPassword').value;
        const confirmPassword = document.getElementById('confirmPassword').value;
        
        if (newPassword || confirmPassword || currentPassword) {
            if (!currentPassword) {
                document.getElementById('currentPasswordError').textContent = 'Debes ingresar tu contraseña actual';
                document.getElementById('currentPasswordError').style.display = 'block';
                isValid = false;
            }
            
            if (newPassword.length > 0 && newPassword.length < 8) {
                document.getElementById('passwordError').style.display = 'block';
                isValid = false;
            }
            
            if (newPassword !== confirmPassword) {
                document.getElementById('confirmPasswordError').style.display = 'block';
                isValid = false;
            }
        }
        
        return isValid;
    }
    
    // Enviar formulario
    updateForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Ocultar alertas previas
        successAlert.style.display = 'none';
        errorAlert.style.display = 'none';
        
        if (validateForm()) {
            // Simular envío al servidor
            setTimeout(() => {
                showSuccess('Tus datos se han actualizado correctamente');
                
                // Aquí iría la lógica AJAX para enviar los datos
                console.log('Datos enviados:', {
                    fotoPerfil: profilePictureInput.files[0] || null,
                    nombres: document.getElementById('nombres').value.trim(),
                    apellidos: document.getElementById('apellidos').value.trim(),
                    documento: document.getElementById('documento').value.trim(),
                    email: document.getElementById('email').value.trim(),
                    telefono: document.getElementById('telefono').value.trim(),
                    currentPassword: document.getElementById('currentPassword').value,
                    newPassword: document.getElementById('newPassword').value
                });
            }, 1000);
        }
    });
    
    // Cancelar cambios
    cancelBtn.addEventListener('click', function() {
        if (confirm('¿Estás seguro de que deseas descartar los cambios?')) {
            loadUserData();
            // Limpiar campos de contraseña
            document.getElementById('currentPassword').value = '';
            document.getElementById('newPassword').value = '';
            document.getElementById('confirmPassword').value = '';
            strengthBar.style.width = '0%';
            strengthLabel.textContent = 'Seguridad: baja';
            strengthLabel.style.color = '';
        }
    });
    
    // Inicializar
    loadUserData();
});

