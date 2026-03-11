/**
 * Componente de Calendario Unificado para ProgSENA
 * 
 * Modos de operación:
 * - 'editable': Permite agregar, editar y eliminar horarios (Coordinador - Crear/Editar)
 * - 'readonly': Solo visualización con detalles (Coordinador - Ver Detalles, Instructor - Mis Horarios)
 */

class CalendarioProgSENA {
    constructor(elementId, config = {}) {
        this.elementId = elementId;
        this.element = document.getElementById(elementId);
        
        if (!this.element) {
            console.error(`Elemento con ID "${elementId}" no encontrado`);
            return;
        }

        // Configuración por defecto
        this.config = {
            modo: config.modo || 'readonly', // 'editable' o 'readonly'
            eventos: config.eventos || [],
            fechaInicio: config.fechaInicio || null,
            onDateClick: config.onDateClick || null,
            onEventClick: config.onEventClick || null,
            onEventDelete: config.onEventDelete || null,
            validaciones: config.validaciones || {},
            ...config
        };

        this.calendar = null;
        this.init();
    }

    init() {
        const calendarConfig = {
            initialView: 'dayGridMonth',
            locale: 'es',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek'
            },
            buttonText: {
                today: 'Hoy',
                month: 'Mes',
                week: 'Semana',
                day: 'Día',
                list: 'Lista'
            },
            height: 'auto',
            fixedWeekCount: false,
            showNonCurrentDates: false,
            navLinks: false,
            slotMinTime: '06:00:00',
            slotMaxTime: '22:00:00',
            slotDuration: '01:00:00',
            allDaySlot: false,
            nowIndicator: true,
            businessHours: {
                daysOfWeek: [1, 2, 3, 4, 5, 6],
                startTime: '06:00',
                endTime: '22:00',
            },
            hiddenDays: [0], // Ocultar domingos
            events: this.config.eventos,
            dayMaxEvents: 4,
            moreLinkText: (num) => '+' + num + ' más',
            
            // Configuración específica según el modo
            selectable: this.config.modo === 'editable',
            editable: false,
            
            // Event handlers
            dateClick: (info) => this.handleDateClick(info),
            eventClick: (info) => this.handleEventClick(info),
            moreLinkClick: (info) => this.handleMoreLinkClick(info),
            eventDidMount: (info) => this.handleEventDidMount(info),
            dayCellDidMount: (info) => this.handleDayCellDidMount(info)
        };

        // Configurar rango válido si hay fecha de inicio
        if (this.config.fechaInicio) {
            calendarConfig.validRange = { start: this.config.fechaInicio };
            calendarConfig.initialDate = this.config.fechaInicio;
        }

        this.calendar = new FullCalendar.Calendar(this.element, calendarConfig);
        this.calendar.render();

        // Listener para redimensionar
        this.setupResizeListeners();
    }

    handleDateClick(info) {
        if (this.config.modo !== 'editable') return;

        const fechaSeleccionada = info.dateStr;
        
        // Validar fecha de inicio
        if (this.config.fechaInicio && info.date < new Date(this.config.fechaInicio + 'T00:00:00')) {
            return;
        }
        
        // No permitir domingos
        if (info.date.getDay() === 0) {
            return;
        }
        
        // Verificar si hay eventos en el día
        const eventosDelDia = this.config.eventos.filter(e => 
            e.start.split('T')[0] === fechaSeleccionada
        );
        
        if (eventosDelDia.length > 0) {
            this.mostrarDetallesDia(fechaSeleccionada);
        } else if (this.config.onDateClick) {
            this.config.onDateClick(fechaSeleccionada);
        }
    }

    handleEventClick(info) {
        const evento = info.event;
        const fecha = evento.startStr.split('T')[0];
        
        if (this.config.modo === 'editable') {
            this.mostrarDetallesDia(fecha);
        } else if (this.config.onEventClick) {
            this.config.onEventClick(evento);
        } else {
            // Modal por defecto para modo readonly
            this.mostrarModalEvento(evento);
        }
        
        info.jsEvent.preventDefault();
    }

    handleMoreLinkClick(info) {
        const fecha = info.date.toISOString().split('T')[0];
        this.mostrarDetallesDia(fecha);
        return 'popover';
    }

    handleEventDidMount(info) {
        info.el.classList.add('evento-calendario');
        
        const props = info.event.extendedProps;
        let tooltipText = `${props.horaTexto || ''}`;
        if (props.competencia) {
            tooltipText += `\n${props.competencia}`;
        }
        if (props.ficha) {
            tooltipText += `\nFicha: ${props.ficha}`;
        }
        info.el.title = tooltipText;
    }

    handleDayCellDidMount(info) {
        // NO aplicar estilos inline - dejar que CSS maneje todo
    }

    setupResizeListeners() {
        window.addEventListener('resize', () => {
            if (this.calendar) {
                this.calendar.updateSize();
            }
        });
        
        const mainContent = document.querySelector('main') || document.querySelector('.main-content');
        if (mainContent) {
            const resizeObserver = new ResizeObserver(() => {
                if (this.calendar) {
                    setTimeout(() => {
                        this.calendar.updateSize();
                    }, 300);
                }
            });
            resizeObserver.observe(mainContent);
        }
    }

    mostrarDetallesDia(fecha) {
        const [year, month, day] = fecha.split('-');
        const fechaObj = new Date(year, month - 1, day);
        const opciones = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
        const fechaFormateada = fechaObj.toLocaleDateString('es-ES', opciones);

        const eventosDelDia = this.config.eventos.filter(e => 
            e.start.split('T')[0] === fecha
        );

        let contenido = `
        <div class="bg-gradient-to-r from-primary to-primary-hover p-6 -m-6 mb-6 rounded-t-lg">
            <div class="flex items-center text-white">
                <i class="fas fa-calendar-day text-3xl mr-4"></i>
                <div>
                    <h3 class="text-xl font-bold">${fechaFormateada}</h3>
                    <p class="text-sm opacity-90 mt-1">Horarios programados para este día</p>
                </div>
            </div>
        </div>`;

        if (eventosDelDia.length > 0) {
            // Ordenar eventos por hora
            eventosDelDia.sort((a, b) => {
                const horaA = a.extendedProps.horaInicio || a.start.split('T')[1];
                const horaB = b.extendedProps.horaInicio || b.start.split('T')[1];
                return horaA.localeCompare(horaB);
            });

            contenido += `
            <div class="overflow-x-auto rounded-lg border border-gray-200 shadow-sm">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gradient-to-r from-gray-50 to-gray-100">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                                Horario
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                                Tipo
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                                Competencia
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                                Ficha
                            </th>`;
            
            if (this.config.modo === 'editable') {
                contenido += `
                            <th class="px-4 py-3 text-center text-xs font-bold text-gray-700 uppercase tracking-wider">
                                Acción
                            </th>`;
            }
            
            contenido += `
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">`;

            eventosDelDia.forEach((evento, idx) => {
                const props = evento.extendedProps;
                const rowBg = idx % 2 === 0 ? 'bg-white' : 'bg-gray-50';
                const horaInicio = props.horaInicio || evento.start.split('T')[1].substring(0, 5);
                const horaFin = props.horaFin || evento.end.split('T')[1].substring(0, 5);
                
                contenido += `
                <tr class="${rowBg} hover:bg-opacity-50 transition-colors duration-150" style="border-left: 4px solid ${evento.borderColor};">
                    <td class="px-4 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 h-10 w-10 flex items-center justify-center rounded-full" style="background-color: ${evento.backgroundColor};">
                                <i class="fas fa-clock text-sm" style="color: ${evento.borderColor};"></i>
                            </div>
                            <div class="ml-3">
                                <div class="text-sm font-bold" style="color: ${evento.borderColor};">${horaInicio}</div>
                                <div class="text-xs text-gray-500">${horaFin}</div>
                            </div>
                        </div>
                    </td>
                    <td class="px-4 py-4">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold" style="background-color: ${evento.backgroundColor}; color: ${evento.borderColor};">
                            <i class="fas ${props.icon || 'fa-calendar'} mr-2"></i>
                            ${props.label || 'Clase'}
                        </span>
                    </td>
                    <td class="px-4 py-4">
                        <div class="text-sm font-medium text-gray-900">${props.competencia || 'N/A'}</div>
                    </td>
                    <td class="px-4 py-4">
                        <div class="text-sm text-gray-700">${props.ficha || 'N/A'}</div>
                    </td>`;
                
                if (this.config.modo === 'editable') {
                    if (props.eliminable && this.config.onEventDelete) {
                        contenido += `
                    <td class="px-4 py-4 text-center">
                        <button type="button" onclick="window.calendarioInstance.eliminarEvento('${fecha}', ${props.index})" 
                                class="inline-flex items-center px-3 py-1 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors duration-150" 
                                title="Eliminar">
                            <i class="fas fa-trash mr-1"></i>
                            Eliminar
                        </button>
                    </td>`;
                    } else {
                        contenido += `
                    <td class="px-4 py-4 text-center">
                        <span class="text-gray-400 text-xs italic">No editable</span>
                    </td>`;
                    }
                }
                
                contenido += `
                </tr>`;
            });

            contenido += `
                    </tbody>
                </table>
            </div>`;
        } else {
            contenido += `
            <div class="text-center py-12 bg-gray-50 rounded-lg border-2 border-dashed border-gray-300">
                <i class="fas fa-calendar-times text-5xl text-gray-300 mb-4"></i>
                <p class="text-lg font-medium text-gray-600">No hay asignaciones programadas</p>
                <p class="text-sm text-gray-500 mt-2">Este día está disponible para programar clases</p>
            </div>`;
        }

        // Botón para agregar clase (solo en modo editable)
        if (this.config.modo === 'editable' && this.config.onDateClick) {
            const fechaInicioStr = this.config.fechaInicio;
            if (fechaInicioStr) {
                const [yearInicio, monthInicio, dayInicio] = fechaInicioStr.split('-');
                const fechaInicioObj = new Date(yearInicio, monthInicio - 1, dayInicio);
                const diaSemana = fechaObj.getDay();

                let horasOcupadas = 0;
                eventosDelDia.forEach(evento => {
                    const inicio = new Date(`2000-01-01T${evento.extendedProps.horaInicio || '00:00'}`);
                    const fin = new Date(`2000-01-01T${evento.extendedProps.horaFin || '00:00'}`);
                    horasOcupadas += (fin - inicio) / (1000 * 60 * 60);
                });

                if (fechaObj >= fechaInicioObj && diaSemana !== 0 && horasOcupadas < 16) {
                    contenido += `
                    <div class="mt-6 pt-6 border-t-2 border-gray-200">
                        <button type="button" onclick="window.calendarioInstance.cerrarModalDetalles(); window.calendarioInstance.config.onDateClick('${fecha}')" 
                                class="w-full flex items-center justify-center px-6 py-3 border border-transparent text-base font-medium rounded-lg text-white bg-gradient-to-r from-primary to-primary-hover hover:from-primary-hover hover:to-primary shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-150">
                            <i class="fas fa-plus-circle mr-2 text-xl"></i>
                            Agregar Clase en este Día
                        </button>
                    </div>`;
                } else if (horasOcupadas >= 16) {
                    contenido += `
                    <div class="mt-6 pt-6 border-t-2 border-gray-200">
                        <div class="text-center py-3 bg-gray-100 rounded-lg border border-gray-300">
                            <i class="fas fa-info-circle text-gray-500 mr-2"></i>
                            <span class="text-gray-600 font-medium">Este día ya tiene todas las horas ocupadas</span>
                        </div>
                    </div>`;
                }
            }
        }

        this.mostrarModal(contenido, 'detalles');
    }

    mostrarModalEvento(evento) {
        const props = evento.extendedProps;
        const fecha = new Date(evento.start);
        const opciones = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
        const fechaFormateada = fecha.toLocaleDateString('es-ES', opciones);
        
        const contenido = `
            <div class="space-y-4">
                <div class="bg-gradient-to-r from-primary to-primary-hover p-4 -m-6 mb-4 rounded-t-lg">
                    <h3 class="text-xl font-bold text-white">
                        <i class="fas fa-calendar-day mr-2"></i>${fechaFormateada}
                    </h3>
                </div>
                <div class="border-l-4 border-primary bg-primary bg-opacity-5 p-4 rounded">
                    <p class="font-bold text-xl text-gray-900 mb-3">${props.competencia || evento.title}</p>
                    <div class="space-y-2">
                        <p class="text-sm text-gray-700">
                            <i class="fas fa-clock mr-2 text-primary"></i>
                            <span class="font-medium">Horario:</span> ${props.horaInicio || ''} - ${props.horaFin || ''}
                        </p>
                        ${props.ambiente ? `
                        <p class="text-sm text-gray-700">
                            <i class="fas fa-door-open mr-2 text-primary"></i>
                            <span class="font-medium">Ambiente:</span> ${props.ambiente}
                        </p>` : ''}
                        ${props.ficha ? `
                        <p class="text-sm text-gray-700">
                            <i class="fas fa-id-card mr-2 text-primary"></i>
                            <span class="font-medium">Ficha:</span> ${props.ficha}
                        </p>` : ''}
                    </div>
                </div>
            </div>
        `;
        
        this.mostrarModal(contenido, 'evento');
    }

    mostrarModal(contenido, tipo = 'detalles') {
        const modalId = tipo === 'detalles' ? 'modalDetallesDiaCalendario' : 'modalEventoCalendario';
        let modal = document.getElementById(modalId);
        
        if (!modal) {
            modal = document.createElement('div');
            modal.id = modalId;
            modal.className = 'fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4';
            modal.style.display = 'none';
            document.body.appendChild(modal);
        }
        
        const maxWidth = tipo === 'detalles' ? 'max-w-3xl' : 'max-w-2xl';
        const maxHeight = tipo === 'detalles' ? 'max-h-[75vh]' : '';
        
        modal.innerHTML = `
            <div class="bg-white rounded-xl shadow-2xl ${maxWidth} w-full transform transition-all ${maxHeight} overflow-y-auto">
                <div class="p-6">
                    ${contenido}
                </div>
                <div class="bg-gray-50 px-6 py-4 rounded-b-xl flex justify-end">
                    <button onclick="window.calendarioInstance.cerrarModal('${modalId}')" 
                            class="px-6 py-2 bg-primary text-white rounded-lg hover:bg-primary-hover transition">
                        Cerrar
                    </button>
                </div>
            </div>
        `;
        
        modal.style.display = 'flex';
        
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                this.cerrarModal(modalId);
            }
        });
    }

    cerrarModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.style.display = 'none';
        }
    }

    cerrarModalDetalles() {
        this.cerrarModal('modalDetallesDiaCalendario');
    }

    eliminarEvento(fecha, index) {
        if (this.config.onEventDelete) {
            this.config.onEventDelete(fecha, index);
        }
    }

    // Métodos públicos para actualizar el calendario
    actualizarEventos(eventos) {
        this.config.eventos = eventos;
        if (this.calendar) {
            this.calendar.removeAllEvents();
            this.calendar.addEventSource(eventos);
        }
    }

    irAFecha(fecha) {
        if (this.calendar) {
            this.calendar.gotoDate(fecha);
        }
    }

    actualizarRangoValido(fechaInicio) {
        this.config.fechaInicio = fechaInicio;
        if (this.calendar) {
            this.calendar.setOption('validRange', { start: fechaInicio });
            this.calendar.gotoDate(fechaInicio);
        }
    }

    destruir() {
        if (this.calendar) {
            this.calendar.destroy();
            this.calendar = null;
        }
    }

    refrescar() {
        if (this.calendar) {
            this.calendar.refetchEvents();
            this.calendar.render();
        }
    }
}

// Hacer disponible globalmente
window.CalendarioProgSENA = CalendarioProgSENA;
