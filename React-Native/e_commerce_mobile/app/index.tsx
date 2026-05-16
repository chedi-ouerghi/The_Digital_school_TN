
import { useFonts, PlayfairDisplay_400Regular, PlayfairDisplay_700Bold } from '@expo-google-fonts/playfair-display';
import { router } from 'expo-router';
import React from 'react';
import { Inter_400Regular, Inter_500Medium, Inter_600SemiBold } from '@expo-google-fonts/inter';
import {
  View,
  Text,
  ScrollView,
  StyleSheet,
  TouchableOpacity,
  StatusBar,
  Dimensions
} from 'react-native';
import { luxuryStyles } from '../styles/luxuryStyles';
import HeroSection from '../components/HeroSection';
import Animated, { useSharedValue, useAnimatedScrollHandler, useAnimatedStyle, interpolate } from 'react-native-reanimated';

const { height } = Dimensions.get('window');

export default function LandingPage() {
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

  const animatedStyle = useAnimatedStyle(() => {
    const opacity = interpolate(scrollY.value, [0, height / 4], [1, 0]);
    const scale = interpolate(scrollY.value, [0, height / 4], [1, 0.9]);
    return {
      opacity,
      transform: [{ scale }],
    };
  });

  if (!fontsLoaded) {
    return null;
  }

  return (
    <View style={luxuryStyles.container}>
      <StatusBar barStyle="light-content" />
      <Animated.ScrollView
        onScroll={scrollHandler}
        scrollEventThrottle={16}
        showsVerticalScrollIndicator={false}
      >
        <HeroSection />
        <View style={styles.contentContainer}>
          <Animated.View style={[styles.section, animatedStyle]}>
            <Text style={styles.sectionTitle}>Discover Our Collection</Text>
            <Text style={styles.sectionText}>
              Experience the epitome of luxury with our exclusive collection of sunglasses. Each pair is crafted with precision and passion, ensuring a timeless appeal and unparalleled quality.
            </Text>
          </Animated.View>

          <Animated.View style={[styles.section]}>
            <Text style={styles.sectionTitle}>Crafted for Excellence</Text>
            <Text style={styles.sectionText}>
              Our artisans combine traditional techniques with modern innovation to create sunglasses that are not just accessories, but a statement of style and sophistication.
            </Text>
          </Animated.View>

          <TouchableOpacity
            style={styles.button}
            onPress={() => router.push('/products')}
          >
            <Text style={styles.buttonText}>Explore Products</Text>
          </TouchableOpacity>
        </View>
      </Animated.ScrollView>
    </View>
  );
}

const styles = StyleSheet.create({
  contentContainer: {
    padding: 20,
    backgroundColor: '#000',
  },
  section: {
    marginBottom: 40,
    padding: 20,
    backgroundColor: '#111',
    borderRadius: 10,
  },
  sectionTitle: {
    fontSize: 28,
    fontFamily: 'PlayfairDisplay_700Bold',
    color: '#FFF',
    marginBottom: 15,
    textAlign: 'center',
  },
  sectionText: {
    fontSize: 16,
    fontFamily: 'Inter_400Regular',
    color: '#DDD',
    textAlign: 'center',
    lineHeight: 24,
  },
  button: {
    backgroundColor: '#FFF',
    paddingVertical: 15,
    paddingHorizontal: 30,
    borderRadius: 30,
    alignSelf: 'center',
    marginTop: 20,
  },
  buttonText: {
    color: '#000',
    fontSize: 18,
    fontFamily: 'Inter_600SemiBold',
  },
});
