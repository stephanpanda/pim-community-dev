import Channel from 'akeneoreferenceentity/domain/model/channel';

export const channelsReceived = (channels: Channel[]) => {
  return {type: 'CHANNELS_RECEIVED', channels};
};