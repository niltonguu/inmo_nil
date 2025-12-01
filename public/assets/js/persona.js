// public/assets/js/persona.js

if (window.personasModuleInitialized) {
    console.log('Módulo Personas ya inicializado, no se vuelve a ejecutar.');
} else {
    window.personasModuleInitialized = true;

    $(function () {

        if (!$('#tblPersonas').length) return;

        console.log('Inicializando módulo Personas');

        const isAdmin      = window.APP && APP.currentUser.role === 'admin';
        const canEditLabel = ['admin','usuario'].includes(APP.currentUser.role);

        const LABELS = [
          { value: 'NULL',          text: 'Null',          cls: 'secondary' },
          { value: 'SIN_RESPUESTA', text: 'Sin respuesta', cls: 'secondary' },
          { value: 'CONTACTADO',    text: 'Contactado',    cls: 'info' },
          { value: 'PROSPECTO',     text: 'Prospecto',     cls: 'primary' },
          { value: 'SEPARADO',      text: 'Separado',      cls: 'warning' },
          { value: 'VENDIDO',       text: 'Vendido',       cls: 'success' },
          { value: 'PROBLEMAS',     text: 'Problemas',     cls: 'danger' }
        ];

        function renderLabelBadge(value) {
          const v = value || 'NULL';
          const obj = LABELS.find(l => l.value === v) || LABELS[0];
          return `<span class="badge bg-${obj.cls}">${obj.text}</span>`;
        }

        // Modales
        const $modalPersona = $('#modalPersona');
        const $modalNota    = $('#modalNota');

        const modalPersona = $modalPersona.length ? new bootstrap.Modal($modalPersona[0]) : null;
        const modalNota    = $modalNota.length    ? new bootstrap.Modal($modalNota[0])    : null;

        const $asignadoModal = $('#asignado_modal');

        // Select2 Ubigeo
        function initSelect2Ubigeo() {
            const $ubigeo = $('#id_ubigeo');
            if (!$.fn.select2) return;
            if ($ubigeo.hasClass('select2-hidden-accessible')) {
                $ubigeo.select2('destroy');
            }
            $ubigeo.select2({
                dropdownParent: $modalPersona,
                width: '100%',
                placeholder: '-- Ubigeo --'
            });
        }

        $modalPersona.on('shown.bs.modal', function () {
            initSelect2Ubigeo();
        });

        // Helpers selects con callback
        function fillSelect(url, $sel, valueField, textField, placeholder, selected, done) {
            $.get(url, function (resp) {
                let data;
                try { data = (typeof resp === 'string') ? JSON.parse(resp) : resp; }
                catch(e){ console.error('JSON inválido en:', url, resp); done(); return; }

                $sel.empty();
                if (placeholder) $sel.append('<option value="">'+placeholder+'</option>');

                data.forEach(row => {
                    $sel.append(`<option value="${row[valueField]}">${row[textField]}</option>`);
                });

                if (selected !== undefined && selected !== null && selected !== '') {
                    $sel.val(String(selected));
                }

                done();

            }).fail(xhr => {
                console.error('Error cargando', url, xhr.status, xhr.responseText);
                done();
            });
        }

        function loadSelects(selected = {}, callback) {
            let pending = 3;
            function done(){
                pending--;
                if(pending===0 && typeof callback==='function'){
                    callback();
                }
            }

            fillSelect(
                'index.php?c=api&a=tipo_persona_list',
                $('#tipo_persona'),
                'id','nombre','-- Tipo --',
                selected.tipo_persona,
                done
            );

            fillSelect(
                'index.php?c=api&a=tipo_documentos_list',
                $('#tipo_documento'),
                'id','nombre','-- Documento --',
                selected.tipo_documento,
                done
            );

            fillSelect(
                'index.php?c=api&a=ubigeos_list',
                $('#id_ubigeo'),
                'id','descripcion','-- Ubigeo --',
                selected.id_ubigeo,
                done
            );
        }

        // Usuarios asignables
        let usersAsignables=[];

        function loadUsersAsignables(){
            if(!isAdmin) return;

            $.get('index.php?c=api&a=users_list', function(resp){

                try { usersAsignables = (typeof resp === 'string') ? JSON.parse(resp) : resp; }
                catch(e){ usersAsignables=[]; }

                if($asignadoModal.length){
                    $asignadoModal.empty().append('<option value="">-- Sin asignar --</option>');
                    usersAsignables.forEach(u=>{
                        $asignadoModal.append(`<option value="${u.id}">${u.fullname}</option>`);
                    });
                }

                table.ajax.reload(null,false);
            });
        }

        // DataTable
        const table = $('#tblPersonas').DataTable({
            ajax: 'index.php?c=personas&a=list',
            columns: [
                { data:'id',width:'5%' },
                { data:'nombres' },
                { data:'apellidos' },

                {
                    data:'telefono',
                    render:function(t,type,row){
                        if(!t) return '<span class="text-muted">-</span>';
                        let tel = String(t).replace(/\D/g,'');
                        if(!tel.startsWith('51')) tel = '51'+tel;

                        return `<a href="https://wa.me/${tel}?text=Hola" target="_blank"
                                class="text-success fw-bold click-phone"
                                data-id="${row.id}">
                                ${t}
                                </a>`;
                    }
                },

                { data:'ubigeo_descripcion', defaultContent:'' },
                { data:'estado',width:'8%' },

                // Etiqueta
                {
                    data:'etiqueta',
                    width:'14%',
                    render:function(v,type,row){
                        v = v || 'NULL';
                        if(!canEditLabel) return renderLabelBadge(v);

                        let options = LABELS.map(l=>{
                            const sel = (l.value===v)?'selected':'';
                            return `<option value="${l.value}" ${sel}>${l.text}</option>`;
                        }).join('');

                        return `<select class="form-select form-select-sm select-label"
                                        data-id="${row.id}">
                                    ${options}
                                </select>`;
                    }
                },

                // Asignado
                {
                    data:null,
                    width:'15%',
                    render:function(row){
                        if(!isAdmin){
                            return row.asignado_nombre
                                ? `<span class="small">${row.asignado_nombre}</span>`
                                : '<span class="text-muted small">Sin asignar</span>';
                        }

                        let html=`<select class="form-select form-select-sm select-asignado"
                                            data-id="${row.id}">
                                    <option value="">-- Sin asignar --</option>`;

                        usersAsignables.forEach(u=>{
                            const sel = (row.asignado==u.id)?'selected':'';
                            html+=`<option value="${u.id}" ${sel}>${u.fullname}</option>`;
                        });

                        html+='</select>';
                        return html;
                    }
                },

                {
                    data:'ultima_nota',
                    render:d=> d?d:'<span class="text-muted small">-</span>'
                },

                {
                    data:null,
                    orderable:false,
                    width:'10%',
                    render:r=>`
                        <div class="btn-group btn-group-sm">
                            <button class="btn btn-outline-secondary btn-note" data-id="${r.id}">
                                <i class="bi bi-chat-left-text"></i>
                            </button>
                            <button class="btn btn-outline-primary btn-edit" data-id="${r.id}">
                                <i class="bi bi-pencil-square"></i>
                            </button>
                            <button class="btn btn-outline-danger btn-del" data-id="${r.id}">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    `
                }
            ],
            language:{
                url:"//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json"
            }
        });

        $('.filter').on('click', function(){
            const etiqueta = $(this).data('etiqueta');
            const url = 'index.php?c=personas&a=list' + (etiqueta ? '&etiqueta='+etiqueta : '');

            table.ajax.url(url).load();
        });


        if(isAdmin) loadUsersAsignables();

        // Nuevo
        $('#btnNew').on('click',function(){
            $('#formPersona')[0].reset();
            $('#id').val('');
            $('#etiqueta').val('NULL');
            if(isAdmin) $asignadoModal.val('');

            loadSelects({},function(){
                modalPersona.show();
            });
        });

        // Editar
        $('#tblPersonas').on('click','.btn-edit',function(){
            const id=$(this).data('id');

            $.get('index.php?c=personas&a=get&id='+id,function(resp){

                let p;
                try{ p=(typeof resp==='string')?JSON.parse(resp):resp; }
                catch(e){ Swal.fire('Error','Respuesta inválida','error'); return; }

                loadSelects({
                    tipo_persona:p.tipo_persona,
                    tipo_documento:p.tipo_documento,
                    id_ubigeo:p.id_ubigeo
                },function(){

                    $('#id').val(p.id);
                    $('#numero_documento').val(p.numero_documento);
                    $('#nombres').val(p.nombres);
                    $('#apellidos').val(p.apellidos);
                    $('#telefono').val(p.telefono);
                    $('#email').val(p.email);
                    $('#estado').val(p.estado);
                    $('#etiqueta').val(p.etiqueta||'NULL');

                    if(isAdmin) $asignadoModal.val(p.asignado?String(p.asignado):'');

                    modalPersona.show();
                });
            });
        });

        // Guardar persona
        $('#btnSavePersona').on('click', function () {
            const formData = $('#formPersona').serialize();

            $.post('index.php?c=personas&a=save', formData)
                .done(function (resp) {
                    let r;
                    try {
                        r = (typeof resp === 'string') ? JSON.parse(resp) : resp;
                    } catch (e) {
                        console.error('Respuesta bruto del servidor:', resp);
                        Swal.fire('Error', 'Respuesta inválida', 'error');
                        return;
                    }

                    if (r.status) {
                        Swal.fire({
                            icon: 'success',
                            title: 'OK',
                            text: 'Guardado correctamente'
                        }).then(() => {
                            // recargar tabla DESPUÉS de cerrar el alert
                            if (modalPersona) modalPersona.hide();
                            if (table) table.ajax.reload(null, false);
                        });
                    } else {
                        Swal.fire('Error', r.msg || 'No se pudo guardar', 'error');
                    }
                })
                .fail(function (xhr) {
                    console.error('Error AJAX save:', xhr.status, xhr.responseText);
                    Swal.fire('Error', 'Error de servidor al guardar', 'error');
                });
        });


        // Notas
        $('#tblPersonas').on('click','.btn-note',function(){
            const id=$(this).data('id');
            $('#id_persona_nota').val(id);
            $('#nota_texto').val('');
            $('#historialNotas').html('<div class="text-muted small">Cargando...</div>');

            $.get('index.php?c=personas&a=notes&id='+id,function(resp){

                let notas;
                try{ notas=(typeof resp==='string')?JSON.parse(resp):resp; }
                catch(e){ $('#historialNotas').html('<div>Error</div>'); return;}

                if(!notas.length){
                    $('#historialNotas').html('<div class="text-muted small">Sin notas</div>');
                    return;
                }

                const html = notas
                  .map(n=>`
                        <div class="border rounded p-2 mb-2">
                            <small class="text-muted">${n.created_at}</small>
                            <div>${n.nota}</div>
                        </div>
                    `)
                  .join('');

                $('#historialNotas').html(html);
            });

            modalNota.show();
        });

        $('#btnSaveNota').on('click',function(){
            const id=$('#id_persona_nota').val();
            const nota=$('#nota_texto').val().trim();

            if(!nota){
                Swal.fire('Error','La nota está vacía','error');
                return;
            }

            $.post('index.php?c=personas&a=save_note',{id_persona:id,nota},function(resp){

                let r;
                try{ r=(typeof resp==='string')?JSON.parse(resp):resp; }
                catch(e){ Swal.fire('Error','Respuesta inválida','error'); return;}

                if(r.ok){
                    Swal.fire('OK','Nota guardada','success');
                    modalNota.hide();
                    table.ajax.reload(null,false);
                }else{
                    Swal.fire('Error',r.msg||'No guardó','error');
                }
            });
        });

        // Asignado
        $('#tblPersonas').on('change','.select-asignado',function(){
            if(!isAdmin) return;

            const id=$(this).data('id');
            const asignado=$(this).val();

            $.post('index.php?c=personas&a=assign',{id,asignado},function(resp){

                let r;
                try{ r=(typeof resp==='string')?JSON.parse(resp):resp; }
                catch(e){ Swal.fire('Error','Respuesta inválida','error'); return;}

                if(!r.status){
                    Swal.fire('Error',r.msg||'No se pudo actualizar','error');
                }
            });
        });

        // Etiqueta desde tabla
        $('#tblPersonas').on('change','.select-label',function(){
            const id=$(this).data('id');
            const etiqueta=$(this).val();

            $.post('index.php?c=personas&a=label',{id,etiqueta},function(resp){

                let r;
                try{ r=(typeof resp==='string')?JSON.parse(resp):resp; }
                catch(e){ Swal.fire('Error','Respuesta inválida','error'); return;}

                if(!r.status){
                    Swal.fire('Error',r.msg||'No se pudo actualizar','error');
                }
            });
        });

        // Click teléfono
        $('#tblPersonas').on('click','.click-phone',function(){
            const id=$(this).data('id');
            $.post('index.php?c=personas&a=phone_click',{id},function(){});
        });

    });
}
