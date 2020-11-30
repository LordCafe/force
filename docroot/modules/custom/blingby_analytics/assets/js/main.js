window.trace = function (uc, data) {
  $.post(`/trace/${uc}`, data);
};


window.traceEvent = function(uc, event, entity, timestamp) {
  window.trace(uc, {
    type: 'event',
    event: event,
    entity_id: entity.id || 0,
    entity_title: entity.title || '',
    entity_data: entity,
    timestamp: timestamp
  });
};

window.traceData = function(uc, key, value) {
  window.trace(uc, {
    type: 'metadata',
    meta_name: key,
    meta_value: value,
  });
}


window.traceForm = function(uc, form) {
  window.trace(uc, {
    type: 'form',
    form: form
  });
}