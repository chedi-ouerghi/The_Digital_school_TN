import { useFonts, PlayfairDisplay_400Regular, PlayfairDisplay_700Bold } from '@expo-google-fonts/playfair-display';
import { router } from 'expo-router';
import {
  View,
  Text,
  ScrollView,
  StyleSheet,
  TouchableOpacity,
  StatusBar,
  Dimensions
} from 'react-native';
import React from 'react';
import { Inter_400Regular, Inter_500Medium, Inter_600SemiBold } from '@expo-google-fonts/inter';
import Animated, { useSharedValue, useAnimatedScrollHandler, useAnimatedStyle, interpolate } from 'react-native-reanimated';
import { luxuryStyles } from '../../styles/luxuryStyles';
import HeroSection from '../../components/HeroSection';

const { height } = Dimensions.get('window');

const LandingPage = () => {
  const [fontsLoaded] = useFonts({
    PlayfairDisplay_400Regular,
    PlayfairDisplay_700Bold,
    Inter_400Regular,
    Inter_500Medium,
    Inter_600SemiBold,
  });

  const scrollY = useSharedValue(0);

  const scrollHandler = useAnimatedScrollHandler((event) => {
    scrollY.value = event.contentOffset.y;
  });

  const animatedStyles = useAnimatedStyle(() => {
    const opacity = interpolate(scrollY.value, [0, height / 4], [1, 0]);
    const translateY = interpolate(scrollY.value, [0, height / 4], [0, -50]);
    return {
      opacity,
      transform: [{ translateY }],
    };
  });

  if (!fontsLoaded) {
    return null;
  }

  return (
    <>
      <StatusBar barStyle="light-content" />
      <Animated.ScrollView
        style={luxuryStyles.container}
        onScroll={scrollHandler}
        scrollEventThrottle={16}
        showsVerticalScrollIndicator={false}
      >
        <HeroSection />
        <View style={styles.content}>
          <Animated.View style={[styles.promoSection, animatedStyles]}>
            <Text style={styles.promoTitle}>Discover Your Signature Look</Text>
            <Text style={styles.promoText}>
              Explore our curated collection of luxury sunglasses, crafted for the discerning individual.
            </Text>
            <TouchableOpacity style={styles.ctaButton} onPress={() => router.push('/products')}>
              <Text style={styles.ctaButtonText}>Explore Collection</Text>
            </TouchableOpacity>
          </Animated.View>

          <View style={styles.featureSection}>
            <Text style={styles.featureTitle}>Why Choose Us?</Text>
            <View style={styles.featureGrid}>
              <View style={styles.featureItem}>
                <Text style={styles.featureItemTitle}>Exquisite Craftsmanship</Text>
                <Text style={styles.featureItemText}>Each pair is a masterpiece of design and quality.</Text>
              </View>
              <View style={styles.featureItem}>
                <Text style={styles.featureItemTitle}>Timeless Elegance</Text>
                <Text style={styles.featureItemText}>Styles that transcend seasons and trends.</Text>
              </View>
              <View style={styles.featureItem}>
                <Text style={styles.featureItemTitle}>Unmatched Protection</Text>
                <Text style={styles.featureItemText}>Superior UV protection for your eyes.</Text>
              </View>
            </View>
          </View>
        </View>
      </Animated.ScrollView>
    </>
  );
};

const styles = StyleSheet.create({
  content: {
    padding: 20,
    backgroundColor: '#fdfaf6',
  },
  promoSection: {
    alignItems: 'center',
    paddingVertical: 60,
    paddingHorizontal: 20,
    backgroundColor: '#fff',
    borderRadius: 20,
    marginBottom: 40,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 10 },
    shadowOpacity: 0.1,
    shadowRadius: 20,
    elevation: 5,
  },
  promoTitle: {
    fontFamily: 'PlayfairDisplay_700Bold',
    fontSize: 32,
    color: '#333',
    textAlign: 'center',
    marginBottom: 15,
  },
  promoText: {
    fontFamily: 'Inter_400Regular',
    fontSize: 16,
    color: '#666',
    textAlign: 'center',
    lineHeight: 24,
    marginBottom: 30,
  },
  ctaButton: {
    backgroundColor: '#c9a959',
    paddingVertical: 15,
    paddingHorizontal: 40,
    borderRadius: 30,
  },
  ctaButtonText: {
    fontFamily: 'Inter_600SemiBold',
    fontSize: 16,
    color: '#fff',
  },
  featureSection: {
    paddingVertical: 40,
  },
  featureTitle: {
    fontFamily: 'PlayfairDisplay_700Bold',
    fontSize: 28,
    color: '#333',
    textAlign: 'center',
    marginBottom: 40,
  },
  featureGrid: {
    // No specific styles needed for the grid container itself
  },
  featureItem: {
    marginBottom: 30,
    alignItems: 'center',
  },
  featureItemTitle: {
    fontFamily: 'Inter_600SemiBold',
    fontSize: 20,
    color: '#333',
    marginBottom: 10,
  },
  featureItemText: {
    fontFamily: 'Inter_400Regular',
    fontSize: 16,
    color: '#666',
    textAlign: 'center',
    lineHeight: 24,
  },
});

export default LandingPage;
