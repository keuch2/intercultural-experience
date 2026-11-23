import React, { useEffect, useRef, useState } from 'react';
import { View, Text, StyleSheet, FlatList, TouchableOpacity, Image, Linking, useWindowDimensions, NativeSyntheticEvent, NativeScrollEvent } from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { AppBanner } from '../services/api/bannerService';

const SIDE = 18;
const GAP = SIDE; // igual al margen: al centrar un banner no asoma el anterior
const AUTO_MS = 6000;

interface Props {
  banners: AppBanner[];
  /** Abrir un programa (cada Inicio sabe a qué pantalla ir). */
  onOpenProgram: (programId: number) => void;
}

/**
 * Carrusel de banners del Inicio (gestionados en el admin). Imagen 2:1 o tarjeta de texto con color;
 * avanza solo cada 6 s mientras no se toque.
 */
const BannerCarousel: React.FC<Props> = ({ banners, onOpenProgram }) => {
  const { width } = useWindowDimensions();
  const itemWidth = width - SIDE * 2;
  const listRef = useRef<FlatList<AppBanner>>(null);
  const [index, setIndex] = useState(0);
  const [paused, setPaused] = useState(false);

  useEffect(() => {
    if (banners.length < 2 || paused) return;
    const t = setInterval(() => {
      setIndex((i) => {
        const next = (i + 1) % banners.length;
        listRef.current?.scrollToOffset({ offset: next * (itemWidth + GAP), animated: true });
        return next;
      });
    }, AUTO_MS);
    return () => clearInterval(t);
  }, [banners.length, paused, itemWidth]);

  if (!banners.length) return null;

  const open = (b: AppBanner) => {
    if (!b.link) return;
    if (b.link.type === 'program') onOpenProgram(b.link.program_id);
    else Linking.openURL(b.link.url).catch(() => {});
  };

  const onScrollEnd = (e: NativeSyntheticEvent<NativeScrollEvent>) => {
    setIndex(Math.round(e.nativeEvent.contentOffset.x / (itemWidth + GAP)));
  };

  return (
    <View style={styles.wrap}>
      <FlatList
        ref={listRef}
        data={banners}
        keyExtractor={(b) => String(b.id)}
        horizontal
        showsHorizontalScrollIndicator={false}
        snapToInterval={itemWidth + GAP}
        decelerationRate="fast"
        contentContainerStyle={{ paddingHorizontal: SIDE }}
        ItemSeparatorComponent={() => <View style={{ width: GAP }} />}
        onScrollBeginDrag={() => setPaused(true)}
        onMomentumScrollEnd={onScrollEnd}
        renderItem={({ item }) => (
          <TouchableOpacity
            activeOpacity={item.link ? 0.9 : 1}
            disabled={!item.link}
            onPress={() => open(item)}
            style={{ width: itemWidth }}
            accessibilityRole={item.link ? 'button' : 'image'}
            accessibilityLabel={item.title || 'Banner'}
          >
            {item.type === 'image' && item.image_url ? (
              <Image source={{ uri: item.image_url }} style={styles.image} resizeMode="cover" />
            ) : (
              <View style={[styles.textCard, { backgroundColor: item.background_color || '#E52224' }]}>
                {!!item.badge && (
                  <View style={styles.badge}>
                    <Ionicons name="star" size={11} color="#fff" />
                    <Text style={styles.badgeText} numberOfLines={1}>{item.badge}</Text>
                  </View>
                )}
                {!!item.title && <Text style={styles.title} numberOfLines={2}>{item.title}</Text>}
                {!!item.subtitle && <Text style={styles.subtitle} numberOfLines={2}>{item.subtitle}</Text>}
                {!!item.link && (
                  <View style={styles.cta}>
                    <Text style={styles.ctaText}>{item.button_label || 'Conocé más'}</Text>
                    <Ionicons name="arrow-forward" size={16} color="#fff" />
                  </View>
                )}
              </View>
            )}
          </TouchableOpacity>
        )}
      />
      {banners.length > 1 && (
        <View style={styles.dots}>
          {banners.map((b, i) => <View key={b.id} style={[styles.dot, i === index && styles.dotActive]} />)}
        </View>
      )}
    </View>
  );
};

const styles = StyleSheet.create({
  wrap: { marginTop: 12, marginBottom: 4 },
  image: { width: '100%', aspectRatio: 2, borderRadius: 16, backgroundColor: '#EEE' },
  textCard: { borderRadius: 16, paddingHorizontal: 20, paddingVertical: 14, aspectRatio: 2, justifyContent: 'center', overflow: 'hidden' },
  badge: { flexDirection: 'row', alignItems: 'center', alignSelf: 'flex-start', backgroundColor: 'rgba(255,255,255,0.22)', paddingHorizontal: 10, paddingVertical: 3, borderRadius: 12, marginBottom: 8, gap: 4 },
  badgeText: { color: '#fff', fontSize: 11, fontWeight: '700', letterSpacing: 0.5 },
  title: { color: '#fff', fontSize: 21, fontWeight: '800', lineHeight: 25 },
  subtitle: { color: '#fff', opacity: 0.95, fontSize: 14, lineHeight: 20, marginTop: 6 },
  cta: { flexDirection: 'row', alignItems: 'center', gap: 6, marginTop: 10 },
  ctaText: { color: '#fff', fontWeight: '700', fontSize: 15 },
  dots: { flexDirection: 'row', justifyContent: 'center', marginTop: 10, gap: 6 },
  dot: { width: 6, height: 6, borderRadius: 3, backgroundColor: '#D4D4D8' },
  dotActive: { width: 18, backgroundColor: '#E52224' },
});

export default BannerCarousel;
